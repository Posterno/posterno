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
					'rpage_id'    => $dashboard_page,
				],
				get_permalink( $login_page )
			);
			wp_safe_redirect( $login_page );
			exit;
		}
	}

}
add_action( 'template_redirect', 'pno_restrict_dashboard_access' );

/**
 * Display a restricted access message at the top of the login form,
 * when a "restricted" query string is available within the url.
 *
 * @param string $form
 * @return void
 */
function pno_display_restricted_access_message( $form ) {

	$page_id    = isset( $_GET['rpage_id'] ) ? absint( $_GET['rpage_id'] ) : false;
	$restricted = isset( $_GET['restricted'] ) ? true : false;

	if ( ! $page_id || ! $restricted ) {
		return;
	}

	$page_title = get_post_field( 'post_title', $page_id );

	$message = apply_filters(
		'pno_login_form_restricted_message',
		sprintf( __( 'You need to be logged in to access the "%1$s" page. Please login below or <a href="%2$s">register</a>.' ), $page_title, get_permalink( pno_get_registration_page_id() ) )
	);

	$data = [
		'message' => $message,
		'type'    => 'warning',
	];

	posterno()->templates
		->set_template_data( $data )
		->get_template_part( 'message' );

}
add_action( 'pno_before_login_form', 'pno_display_restricted_access_message', 10, 2 );

/**
 * Loads the content for the dashboard tab called "dashboard".
 * By default this is the first tab.
 *
 * @return void
 */
function pno_load_initial_dashboard_content() {

	$data = [
		'user' => wp_get_current_user(),
	];

	posterno()->templates
		->set_template_data( $data )
		->get_template_part( 'logged-user' );

}
add_action( 'pno_dashboard_tab_content_dashboard', 'pno_load_initial_dashboard_content' );

/**
 * Load the content for the account details tab within the dashboard page.
 *
 * @return void
 */
function pno_load_dashboard_account_details() {

	echo posterno()->forms->get_form( 'account', [] );

}
add_action( 'pno_dashboard_tab_content_edit-account', 'pno_load_dashboard_account_details' );

/**
 * Adds markup to form fields for the account page so we can make the
 * first and last name fields on 2 columns layout.
 *
 * @param string $key
 * @param array $field
 * @param string $form
 * @param string $step
 * @return void
 */
function pno_account_form_make_row_opening( $key, $field, $form, $step ) {

	if ( $form == 'account' && $key == 'first_name' ) {
		echo '<div class="row">';
	}

}
add_action( 'pno_form_before_field', 'pno_account_form_make_row_opening', 10, 4 );

/**
 * Adds markup to form fields for the account page so we can make the
 * first and last name fields on 2 columns layout.
 *
 * @param string $key
 * @param array $field
 * @param string $form
 * @param string $step
 * @return void
 */
function pno_account_form_make_row_closing( $key, $field, $form, $step ) {

	if ( $form == 'account' && $key == 'last_name' ) {
		echo '</div>';
	}

}
add_action( 'pno_form_after_field', 'pno_account_form_make_row_closing', 10, 4 );
