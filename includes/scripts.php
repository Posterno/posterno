<?php
/**
 * Scripts and styles registration.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Load scripts and styles for the admin panel.
 *
 * @return void
 */
function pno_load_admin_scripts() {

	$screen  = get_current_screen();
	$js_dir  = PNO_PLUGIN_URL . 'assets/js/';
	$css_dir = PNO_PLUGIN_URL . 'assets/css/';
	$version = PNO_VERSION;

	wp_register_style( 'pno-logo', $css_dir . 'posterno-font.css', array(), $version );
	wp_enqueue_style( 'pno-logo' );

	if ( defined( 'PNO_VUE_DEV' ) && PNO_VUE_DEV === true ) {

		// Register settings page scripts.
		wp_register_script( 'pno-settings-page', 'http://localhost:8080/options-panel.js', [], PNO_VERSION, true );
		// Register the custom fields page scripts.
		wp_register_script( 'pno-custom-fields-page', 'http://localhost:8080/custom-fields-editor.js', [], PNO_VERSION, true );

	} else {

	}

	// Load script for the settings page.
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-settings' ) {
		wp_enqueue_script( 'pno-settings-page' );
		wp_localize_script( 'pno-settings-page', 'pno_settings_page', pno_get_settings_page_vars() );
	}

	// Load script for the custom fields page.
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-custom-fields' ) {
		wp_enqueue_script( 'pno-custom-fields-page' );
		wp_localize_script( 'pno-custom-fields-page', 'pno_fields_editor', pno_get_custom_fields_editor_js_vars() );
	}

	if ( $screen->id == 'pno_users_fields' ) {
		wp_enqueue_style( 'pnocf', PNO_PLUGIN_URL . '/assets/css/pno-custom-fields-cpt.min.css', [], PNO_VERSION );
		wp_enqueue_script( 'pnocf-validation', PNO_PLUGIN_URL . '/assets/js/pno-profile-custom-fields-admin-validation.min.js', [], PNO_VERSION, true );
		wp_localize_script( 'pnocf-validation', 'pno_user_cf', pno_get_users_custom_fields_page_vars() );
	}

}
add_action( 'admin_enqueue_scripts', 'pno_load_admin_scripts', 100 );

/**
 * Load Posterno's frontend styles and scripts.
 *
 * @return void
 */
function pno_load_frontend_scripts() {

	// Register the scripts
	wp_register_style( 'pno-bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css', [], PNO_VERSION );
	wp_register_script( 'pno-bootstrap-script', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js', [ 'jquery' ], PNO_VERSION, true );
	wp_register_script( 'pno-bootstrap-script-popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js', [ 'jquery' ], PNO_VERSION, true );

	wp_enqueue_script( 'jquery' );

	// Load the required style only if enabled.
	if ( pno_get_option( 'bootstrap_style' ) ) {
		wp_enqueue_style( 'pno-bootstrap' );
	}

	// Load the required scripts only if enabled.
	if ( pno_get_option( 'bootstrap_script' ) ) {
		wp_enqueue_script( 'pno-bootstrap-script-popper' );
		wp_enqueue_script( 'pno-bootstrap-script' );
	}

	// Register pno's own stylesheet.
	wp_register_style( 'pno', PNO_PLUGIN_URL . 'assets/css/pno.min.css', [], PNO_VERSION );
	wp_enqueue_style( 'pno' );

}
add_action( 'wp_enqueue_scripts', 'pno_load_frontend_scripts' );
