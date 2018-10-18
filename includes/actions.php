<?php
/**
 * List of actions that interact with WordPress.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Load stuff after theme setup.
 *
 * @return void
 */
function pno_after_theme_setup_load() {

	\Carbon_Fields\Carbon_Fields::boot();

	register_nav_menu( 'pno-dashboard-menu', esc_html__( 'Posterno Dashboard Menu' ) );

}
add_action( 'after_setup_theme', 'pno_after_theme_setup_load', 20 );

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
					'redirect_to' => rawurlencode( get_permalink( $dashboard_page ) ),
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
 * @param string $form form object.
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
add_action( 'pno_before_login_form', 'pno_display_restricted_access_message', 10 );

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

	echo do_shortcode( '[pno_account_customization_form]' );

}
add_action( 'pno_dashboard_tab_content_edit-account', 'pno_load_dashboard_account_details' );

/**
 * Load the content for the password tab within the dashboard page.
 *
 * @return void
 */
function pno_load_dashboard_password_details() {

	echo do_shortcode( '[pno_change_password_form]' );

}
add_action( 'pno_dashboard_tab_content_password', 'pno_load_dashboard_password_details' );

/**
 * Load the content for the privacy tab within the dashboard page.
 *
 * @return void
 */
function pno_load_dashboard_privacy() {

	if ( pno_get_option( 'allow_data_request' ) ) {
		echo do_shortcode( '[pno_request_data_form]' );
	}

	if ( pno_get_option( 'allow_data_erasure' ) ) {
		echo do_shortcode( '[pno_request_data_erasure_form]' );
	}

	if ( pno_get_option( 'allow_account_delete' ) ) {
		echo do_shortcode( '[pno_delete_account_form]' );
	}

}
add_action( 'pno_dashboard_tab_content_privacy', 'pno_load_dashboard_privacy' );

/**
 * Load the content for the manage listings tab within the dashboard page.
 *
 * @return void
 */
function pno_load_manage_listings_dashboard() {

	$user_id = get_current_user_id();

	if ( ! pno_user_has_submitted_listings( $user_id ) ) {
		return;
	}

	$listings = pno_get_user_submitted_listings( $user_id );

	posterno()->templates
		->set_template_data(
			[
				'columns'         => pno_get_listings_table_columns(),
				'submission_page' => pno_get_listing_submission_page_id(),
				'listings'        => $listings,
			]
		)
		->get_template_part( 'dashboard/manage', 'listings' );

}
add_action( 'pno_dashboard_tab_content_listings', 'pno_load_manage_listings_dashboard' );

/**
 * Add plugin's version to header.
 *
 * @return void
 */
function pno_version_in_header() {
	echo '<meta name="generator" content="Posterno v' . esc_html( PNO_VERSION ) . '" />' . "\n";
}
add_action( 'wp_head', 'pno_version_in_header' );

/**
 * Adjust labels within the wp-login.php form to match
 * the type of login method selected in PNO.
 *
 * @return void
 */
function pno_adjust_wplogin_form_labels() {

	$login_method    = pno_get_option( 'login_method' );
	$translated_text = esc_html__( 'Username or email address' );

	if ( $login_method === 'username' ) {
		$translated_text = esc_html__( 'Username' );
	} elseif ( $login_method === 'email' ) {
		$translated_text = esc_html__( 'Email' );
	}

	add_filter(
		'gettext', function ( $t, $text, $domain ) use ( $translated_text ) {
			if ( 'Username or Email Address' === $text || 'Username' === $text ) {
				return $translated_text;
			}
			return $t;
		}, 20, 3
	);

}
add_action( 'login_head', 'pno_adjust_wplogin_form_labels' );

/**
 * Redirect users to the login page if they're not registered and
 * accessing the submission page if restricted.
 *
 * @return void
 */
function pno_restrict_access_to_listings_submission_page() {

	$submission_page = pno_get_listing_submission_page_id();

	if ( $submission_page && is_int( $submission_page ) && is_page( $submission_page ) && ! is_user_logged_in() ) {
		$login_page = pno_get_login_page_id();
		if ( $login_page && is_int( $login_page ) ) {
			$login_page = add_query_arg(
				[
					'redirect_to' => rawurlencode( get_permalink( $submission_page ) ),
					'restricted'  => true,
					'rpage_id'    => $submission_page,
				],
				get_permalink( $login_page )
			);
			wp_safe_redirect( $login_page );
			exit;
		}
	}
}
add_action( 'template_redirect', 'pno_restrict_access_to_listings_submission_page' );
