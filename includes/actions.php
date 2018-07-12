<?php
/**
 * List of actions that interact with WordPress.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Lock access to wp-login.php and redirect users to the pno's login page.
 *
 * @return void
 */
function pno_restrict_wp_login() {

	global $pagenow;

	// Check if a $_GET['action'] is set, and if so, load it into $action variable
	$action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : '';

	// Check if we're on the login page, and ensure the action is not 'logout'
	if ( $pagenow == 'wp-login.php' && ! defined( 'PNO_UNLOCK_WP_LOGIN' ) && ( ! $action || ( $action && ! in_array( $action, array( 'logout', 'lostpassword', 'rp', 'resetpass' ) ) ) ) ) {
		$login_page = pno_get_login_page_id();
		if ( $login_page && pno_get_option( 'lock_wp_login' ) ) {
			$page = get_permalink( $login_page );
			wp_safe_redirect( $page );
			exit();
		}
	}

}
add_action( 'init', 'pno_restrict_wp_login' );

/**
 * Restrict access to the dashboard page only to logged in users.
 *
 * @return void
 */
function pno_restrict_dashboard_access() {

	$dashboard_page = pno_get_dashboard_page_id();

	if ( $dashboard_page && is_int( $dashboard_page ) && is_page( $dashboard_page ) && ! is_user_logged_in() ) {
		$login_page = pno_get_login_page_id();
		if ( $login_page && is_int( $login_page ) ) {
			$login_page = add_query_arg(
				[
					'redirect_to' => urlencode( get_permalink( $dashboard_page ) ),
					'restricted'  => true,
				],
				get_permalink( $login_page )
			);
			wp_safe_redirect( $login_page );
			exit;
		}
	}

}
add_action( 'template_redirect', 'pno_restrict_dashboard_access' );
