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
		if ( $login_page ) {
			$page = get_permalink( $login_page );
			wp_safe_redirect( $page );
			exit();
		}
	}

}
add_action( 'init', 'pno_restrict_wp_login' );
