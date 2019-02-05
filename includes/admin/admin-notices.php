<?php
/**
 * Admin notices.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display a notice when the avatar field is disabled and the user is editing the field.
 *
 * @return void
 */
function pno_avatar_field_is_disabled_notice() {

	$screen = get_current_screen();

	if ( $screen instanceof WP_Screen && $screen->id == 'pno_users_fields' ) {

		global $post;

		$field = new PNO\Field\Profile( $post->ID );

		if ( $post instanceof WP_Post && isset( $post->ID ) && $field->get_object_meta_key() === 'avatar' && ! pno_get_option( 'allow_avatars' ) ) {

			$message = esc_html__( 'The avatar field is currently disabled. You can enable it through the plugin\'s settings.' );

			posterno()->admin_notices->register_notice( 'avatar_disabled', 'info', $message, [ 'dismissible' => false ] );

		}
	}

}
add_action( 'admin_head', 'pno_avatar_field_is_disabled_notice' );

/**
 * Display an error notice if the user has wrongly configured the password settings.
 *
 * @return void
 */
function pno_password_settings_controller_notice() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-options' ) {
		return;
	}

	if ( pno_get_option( 'disable_password' ) && pno_get_option( 'verify_password' ) ) {

		$message = esc_html__( 'Posterno: you have enabled the "Disable custom passwords during registration" setting, please disable the "Enable password confirmation" option.' );

		posterno()->admin_notices->register_notice( 'psw_setting_error', 'error', $message, [ 'dismissible' => false ] );

	}

}
add_action( 'admin_head', 'pno_password_settings_controller_notice' );

/**
 * Display a notice if avatars are currently globally disabled onto the site.
 *
 * @return void
 */
function pno_avatar_globally_disabled_notice() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-options' ) {
		return;
	}

	if ( pno_get_option( 'allow_avatars' ) && ! get_option( 'show_avatars' ) ) {

		$admin_url     = admin_url( 'options-discussion.php#show_avatars' );
		$pno_admin_url = admin_url( 'options-general.php?page=posterno-options' );

		$message = sprintf( __( 'Posterno: avatars <a href="%1$s">are currently globally disabled</a> onto your site, please <a href="%1$s">enable avatars</a> onto your site or <a href="%2$s">disable Posterno\'s built-in custom avatars</a>.' ), $admin_url, $pno_admin_url );

		posterno()->admin_notices->register_notice( 'avatar_setting_error', 'error', $message, [ 'dismissible' => false ] );

	}

}
add_action( 'admin_head', 'pno_avatar_globally_disabled_notice' );

/**
 * Display an error message when pages settings have more than one page selected.
 * Dirty solution since Carbon Fields currently doesn't provide a select "search" field,
 * therefore I'm forced to use a multiselect field to allow customers to search options.
 *
 * @return void
 */
function pno_required_pages_settings_is_singular_notice() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-options' ) {
		return;
	}

	$settings = [
		'login_page'                  => esc_html__( 'Login page' ),
		'password_page'               => esc_html__( 'Password recovery page' ),
		'registration_page'           => esc_html__( 'Registration page' ),
		'dashboard_page'              => esc_html__( 'Dashboard page' ),
		'submission_page'             => esc_html__( 'Listing submission page' ),
		'editing_page'                => esc_html__( 'Listing editing page' ),
		'profile_page'                => esc_html__( 'Public profile page' ),
		'terms_page'                  => esc_html__( 'Terms Page:' ),
		'login_redirect'              => esc_html__( 'After login' ),
		'logout_redirect'             => esc_html__( 'After logout' ),
		'registration_redirect'       => esc_html__( 'After registration' ),
		'cancellation_redirect'       => esc_html__( 'After account cancellation' ),
		'listing_submission_redirect' => esc_html__( 'After successful submission' ),
		'listing_editing_redirect'    => esc_html__( 'After successful editing' ),
	];

	$settings = apply_filters( 'pno_singular_page_options_list', $settings );

	foreach ( $settings as $key => $label ) {

		$option = pno_get_option( $key, false );

		if ( is_array( $option ) && count( $option ) > 1 ) {

			$message = sprintf( __( 'Posterno: the setting <strong>"%1$s"</strong> can only have 1 page selected. Please correct the issue by selecting only one page into the <a href="%2$s">settings panel</a>.' ), $label, admin_url( 'options-general.php?page=posterno-options' ) );

			posterno()->admin_notices->register_notice( "pno_setting_error_{$key}", 'error', $message, [ 'dismissible' => false ] );

		}
	}

}
add_action( 'admin_head', 'pno_required_pages_settings_is_singular_notice' );

/**
 * Display an error notice if the permalink structure needs to change.
 *
 * @return void
 */
function pno_permalink_controller_notice() {

	global $wp_rewrite;

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-options' || isset( $_GET['page'] ) && $_GET['page'] === 'pno-getting-started' ) {
		return;
	}

	if ( empty( $wp_rewrite->permalink_structure ) ) {

		$message = sprintf( __( '<strong>Posterno is almost ready</strong>. You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.' ), admin_url( 'options-permalink.php' ) );

		posterno()->admin_notices->register_notice( 'permalink_setting_error', 'error', $message, [ 'dismissible' => false ] );

	}

}
add_action( 'admin_head', 'pno_permalink_controller_notice' );
