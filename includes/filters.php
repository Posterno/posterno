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
		$pno_login_page = add_query_arg( [ 'redirect_to' => urlencode( $redirect ) ], $pno_login_page );
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

/**
 * Modify the url of the wp_lostpassword_url() function.
 *
 * @param string $url
 * @param string $redirect
 * @return void
 */
function pno_set_lostpassword_url( $url, $redirect ) {
	$password_page = pno_get_password_recovery_page_id();
	if ( $password_page ) {
		return esc_url( get_permalink( $password_page ) );
	} else {
		return $url;
	}
}
add_filter( 'lostpassword_url', 'pno_set_lostpassword_url', 10, 2 );

/**
 * Modify the logout url to include redirects set by PNO - if any.
 *
 * @param string $logout_url
 * @param string $redirect
 * @return void
 */
function pno_set_logout_url( $logout_url, $redirect ) {
	$logout_redirect = pno_get_option( 'logout_redirect' );
	if ( ! empty( $logout_redirect ) && is_array( $logout_redirect ) && isset( $logout_redirect['value'] ) && ! $redirect ) {
		$logout_redirect = get_permalink( $logout_redirect['value'] );
		$args            = [
			'action'      => 'logout',
			'redirect_to' => urlencode( $logout_redirect ),
		];
		$logout_url      = add_query_arg( $args, site_url( 'wp-login.php', 'login' ) );
		$logout_url      = wp_nonce_url( $logout_url, 'log-out' );
	}
	return $logout_url;
}
add_filter( 'logout_url', 'pno_set_logout_url', 20, 2 );

/**
 * Filters the upload dir when $pno_upload is true.
 *
 * @since 0.1.0
 * @param  array $pathdata
 * @return array
 */
function pno_upload_dir( $pathdata ) {

	global $pno_upload, $pno_uploading_file;

	if ( ! empty( $pno_upload ) ) {
		$dir = untrailingslashit( apply_filters( 'pno_upload_dir', 'pno-uploads/' . sanitize_key( $pno_uploading_file ), sanitize_key( $pno_uploading_file ) ) );
		if ( empty( $pathdata['subdir'] ) ) {
			$pathdata['path']   = $pathdata['path'] . '/' . $dir;
			$pathdata['url']    = $pathdata['url'] . '/' . $dir;
			$pathdata['subdir'] = '/' . $dir;
		} else {
			$new_subdir         = '/' . $dir . $pathdata['subdir'];
			$pathdata['path']   = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );
			$pathdata['url']    = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );
			$pathdata['subdir'] = $new_subdir;
		}
	}
	return $pathdata;
}
add_filter( 'upload_dir', 'pno_upload_dir' );

/**
 * Adds a class to the first name and last name fields on the account page
 * to make those two fields on 2 columns.
 *
 * @param string $classes
 * @param string $field_key
 * @param string $field
 * @param string $class
 * @return void
 */
function pno_make_account_form_fields_two_columns( $classes, $field_key, $field, $class ) {

	$dashboard_page = pno_get_dashboard_page_id();

	if ( $dashboard_page && is_page( $dashboard_page ) && $field_key == 'first_name' || $field_key == 'last_name' ) {
		$classes[] = 'col-sm-12 col-md-6';
	}

	return $classes;

}
add_filter( 'pno_form_field_classes', 'pno_make_account_form_fields_two_columns', 10, 4 );
