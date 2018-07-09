<?php
/**
 * List of filters that should only trigger on the frontend.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Validate authentication with the selected login method.
 *
 * @param object $user
 * @param string $username
 * @param string $password
 * @return void
 */
function pno_authentication( $user, $username, $password ) {

	$authentication_method = pno_get_option( 'login_method' );

	if ( $authentication_method == 'username' ) {

		if ( is_email( $username ) ) {
			return new WP_Error( 'username_only', __( 'Invalid username or incorrect password.' ) );
		}
		return wp_authenticate_username_password( null, $username, $password );

	} elseif ( $authentication_method == 'email' ) {

		if ( ! empty( $username ) && is_email( $username ) ) {

			$user = get_user_by( 'email', $username );

			if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status ) {
				$username = $user->user_login;
				return wp_authenticate_username_password( null, $username, $password );
			}
		} else {
			return new WP_Error( 'email_only', __( 'Invalid email address or incorrect password.' ) );
		}
	}

	return $user;

}
add_filter( 'authenticate', 'pno_authentication', 20, 3 );

/**
 * Filter the wp_login_url function by using the built-in pno's page.
 *
 * @param string $login_url
 * @param string $redirect
 * @param boolean $force_reauth
 * @return void
 */
function pno_login_url( $login_url, $redirect, $force_reauth ) {
	$pno_login_page = pno_get_login_page_id();
	$pno_login_page = get_permalink( $pno_login_page );
	if ( $redirect ) {
		$pno_login_page = add_query_arg( [ 'redirect_to' => $redirect ], $pno_login_page );
	}
	return $pno_login_page;
}
add_filter( 'login_url', 'pno_login_url', 10, 3 );

/**
 * Modify the url retrieved with wp_registration_url().
 *
 * @param string $url
 * @return void
 */
function pno_set_registration_url( $url ) {
	$registration_page = pno_get_registration_page_id();
	if ( $registration_page ) {
		return esc_url( get_permalink( $registration_page ) );
	} else {
		return $url;
	}
}
add_filter( 'register_url', 'pno_set_registration_url' );
