<?php
/**
 * Scripts and styles registration.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use PNO\CurrencyHelper;

// Exit if accessed directly..
defined( 'ABSPATH' ) || exit;

/**
 * Load scripts and styles for the admin panel.
 *
 * @return void
 */
function pno_load_admin_scripts() {

	$screen  = get_current_screen();
	$js_dir  = PNO_PLUGIN_URL . 'assets/js/admin/';
	$css_dir = PNO_PLUGIN_URL . 'assets/css/admin/';
	$version = PNO_VERSION;

	wp_register_style( 'pno-editors-styling', $css_dir . 'admin-custom-fields-editor.min.css', [], $version );
	wp_register_style( 'pno-editors-styling-post-type', $css_dir . 'admin-custom-fields-cpt.min.css', [], $version );
	wp_register_style( 'pno-getting-started', $css_dir . 'admin-getting-started.min.css', [], $version );
	wp_register_style( 'pno-vendors-chunk', PNO_PLUGIN_URL . 'dist/css/chunk-vendors.css', [], $version );

	if ( defined( 'PNO_VUE_DEV' ) && PNO_VUE_DEV === true ) {

		// Register the custom fields page scripts.
		wp_register_script( 'pno-registration-form-editor', 'http://localhost:8080/registration-form-editor.js', [], $version, true );
		wp_register_script( 'pno-profile-fields-editor', 'http://localhost:8080/profile-fields.js', [], $version, true );
		wp_register_script( 'pno-listings-fields-editor', 'http://localhost:8080/listings-fields-editor.js', [], $version, true );

	} else {

		wp_register_script( 'pno-vue-vendors-chunk', PNO_PLUGIN_URL . 'dist/js/chunk-vendors.js', [], $version, true );
		wp_register_script( 'pno-registration-form-editor', PNO_PLUGIN_URL . 'dist/js/registration-form-editor.js', [ 'pno-vue-vendors-chunk' ], $version, true );
		wp_register_script( 'pno-profile-fields-editor', PNO_PLUGIN_URL . 'dist/js/profile-fields.js', [ 'pno-vue-vendors-chunk' ], $version, true );
		wp_register_script( 'pno-listings-fields-editor', PNO_PLUGIN_URL . 'dist/js/listings-fields-editor.js', [ 'pno-vue-vendors-chunk' ], $version, true );

	}

	// Load scripts for the registration form editor page.
	if ( $screen->id === 'users_page_posterno-custom-registration-form' ) {
		if ( ! defined( 'PNO_VUE_DEV' ) || defined( 'PNO_VUE_DEV' ) && PNO_VUE_DEV !== true ) {
			wp_enqueue_style( 'pno-vendors-chunk' );
		}
		wp_enqueue_style( 'pno-editors-styling' );
		wp_enqueue_script( 'pno-registration-form-editor' );
		wp_localize_script( 'pno-registration-form-editor', 'pno_fields_editor', pno_get_custom_fields_editor_js_vars() );
	}

	// Load scritps for the profile fields editor page.
	if ( $screen->id === 'users_page_posterno-custom-profile-fields' ) {
		if ( ! defined( 'PNO_VUE_DEV' ) || defined( 'PNO_VUE_DEV' ) && PNO_VUE_DEV !== true ) {
			wp_enqueue_style( 'pno-vendors-chunk' );
		}
		wp_enqueue_style( 'pno-editors-styling' );
		wp_enqueue_script( 'pno-profile-fields-editor' );
		wp_localize_script( 'pno-profile-fields-editor', 'pno_fields_editor', pno_get_custom_fields_editor_js_vars() );
	}

	if ( $screen->id === 'listings_page_posterno-custom-listings-fields' ) {
		if ( ! defined( 'PNO_VUE_DEV' ) || defined( 'PNO_VUE_DEV' ) && PNO_VUE_DEV !== true ) {
			wp_enqueue_style( 'pno-vendors-chunk' );
		}
		wp_enqueue_style( 'pno-editors-styling' );
		wp_enqueue_script( 'pno-listings-fields-editor' );
		wp_localize_script( 'pno-listings-fields-editor', 'pno_fields_editor', pno_get_custom_fields_editor_js_vars() );
	}

	if ( $screen->id === 'pno_users_fields' ) {
		if ( ! defined( 'PNO_VUE_DEV' ) || defined( 'PNO_VUE_DEV' ) && PNO_VUE_DEV !== true ) {
			wp_enqueue_style( 'pno-vendors-chunk' );
		}
		wp_enqueue_style( 'pno-editors-styling-post-type' );
	}

	if ( $screen->id === 'pno_listings_fields' ) {
		wp_enqueue_style( 'pno-editors-styling-post-type' );
	}

	if ( $screen->id === 'pno_signup_fields' ) {
		wp_enqueue_style( 'pno-editors-styling-post-type' );
	}

	if ( $screen->id === 'dashboard_page_pno-getting-started' ) {
		wp_enqueue_style( 'pno-getting-started' );
	}

	$admin_style_screens = [
		'edit-listings',
		'edit-listings-types',
		'edit-listings-categories',
		'edit-listings-locations',
		'edit-listings-tags',
		'listings',
		'pno_emails',
	];

	if ( in_array( $screen->id, $admin_style_screens, true ) ) {
		wp_enqueue_style( 'pno-admin-style', PNO_PLUGIN_URL . 'assets/css/admin/admin-listings.min.css', [], $version );
	}

	// Load carbon fields style fixer.
	$is_options_page = pno_starts_with( $screen->id, 'admin_page_posterno-options' ) || pno_starts_with( $screen->id, 'settings_page_posterno' ) || pno_starts_with( $screen->id, 'pno_' );

	if ( $is_options_page || in_array( $screen->id, $admin_style_screens, true ) ) {
		wp_enqueue_style( 'posterno-cb-fixer', PNO_PLUGIN_URL . 'assets/css/admin/carbon.min.css', false, $version );
	}

	// Load styling for tools page.
	if ( $screen->id === 'tools_page_posterno-tools' ) {
		wp_enqueue_style( 'pno-options-panel', PNO_PLUGIN_URL . 'assets/css/admin/admin-settings-panel.min.css', false, PNO_VERSION );
	}

	// Load font for the post type icon.
	wp_enqueue_style( 'pno-font-icon', PNO_PLUGIN_URL . 'assets/css/admin/posterno.min.css', false, PNO_VERSION );

}
add_action( 'admin_enqueue_scripts', 'pno_load_admin_scripts', 100 );

/**
 * Load Posterno's frontend styles and scripts.
 *
 * @return void
 */
function pno_load_frontend_scripts() {

	$version = PNO_VERSION;

	// Register the scripts.
	wp_register_style( 'pno-fa', 'https://use.fontawesome.com/releases/v5.2.0/css/all.css', [], $version );
	wp_register_style( 'pno-select2-style', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css', false, $version );
	wp_register_style( 'pno-flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], $version );
	wp_register_style( 'pno', PNO_PLUGIN_URL . 'assets/css/frontend/posterno.min.css', [], $version );

	wp_register_script( 'pno-bootstrap-script', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js', [ 'jquery' ], $version, true );
	wp_register_script( 'pno-bootstrap-script-popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js', [ 'jquery' ], $version, true );
	wp_register_script( 'pno-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js', array( 'jquery' ), $version, true );
	wp_register_script( 'pno-flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', false, $version, true );
	wp_register_script( 'pno-autonumeric', 'https://unpkg.com/autonumeric', false, $version, true );
	wp_register_script( 'pno-general', PNO_PLUGIN_URL . 'assets/js/frontend/posterno.min.js', array( 'jquery' ), $version, true );

	wp_enqueue_script( 'jquery' );

	// Load the required style only if enabled.
	if ( pno_get_option( 'bootstrap_style' ) ) {
		wp_enqueue_style( 'pno' );
		wp_enqueue_style( 'pno-fa' );
	}

	// Load the required scripts only if enabled.
	if ( pno_get_option( 'bootstrap_script' ) ) {
		wp_enqueue_script( 'pno-bootstrap-script-popper' );
		wp_enqueue_script( 'pno-bootstrap-script' );
	}

	if ( is_page( pno_get_dashboard_page_id() ) || is_page( pno_get_registration_page_id() ) ) {
		wp_enqueue_style( 'pno-select2-style' );
		wp_enqueue_script( 'pno-select2' );
	}

	if ( is_page( pno_get_dashboard_page_id() ) || is_page( pno_get_registration_page_id() ) || is_page( pno_get_listing_submission_page_id() ) || is_page( pno_get_listing_editing_page_id() ) ) {
		wp_enqueue_script( 'pno-autonumeric' );
	}

	// Register pno's own stylesheet.
	wp_enqueue_script( 'pno-general' );

	$js_vars = [
		'bootstrap'                        => (bool) pno_get_option( 'bootstrap_style' ),
		'mapProvider'                      => pno_get_option( 'map_provider', 'googlemaps' ),
		'googleMapsApiKey'                 => pno_get_option( 'google_maps_api_key' ),
		'startingLatitude'                 => pno_get_option( 'map_starting_lat', '40.7484405' ),
		'startingLongitude'                => pno_get_option( 'map_starting_lng', '-73.9944191' ),
		'mapZoom'                          => pno_get_option( 'map_zoom', '12' ),
		'boundsDisabled'                   => pno_get_option( 'disable_bounds_center', false ),
		'internal_links_new_tab'           => (bool) pno_get_option( 'listing_open_new_tab', false ),
		'internal_links_new_tab_selectors' => pno_get_internal_listing_links_selectors(),
		'external_links_new_tab'           => (bool) pno_get_option( 'listing_external_open_new_tab', false ),
		'external_links_rel_attributes'    => (bool) pno_get_option( 'listing_external_rel_attributes', false ),
		'external_links_new_tab_selectors' => pno_get_external_listing_links_selectors(),
		'currency'                         => CurrencyHelper::get_currency_symbol(),
		'currency_thousands_separator'     => CurrencyHelper::get_thousands_separator(),
		'currency_decimal_separator'       => CurrencyHelper::get_decimal_separator(),
		'currency_decimals_number'         => CurrencyHelper::get_decimals(),
		'labels'                           => [
			'requestGeolocation'      => esc_html__( 'Find my location', 'posterno' ),
			'youHere'                 => esc_html__( 'You are here.', 'posterno' ),
			'addressNotFound'         => esc_html__( 'Address not found, please try again.', 'posterno' ),
			'geolocationFailed'       => esc_html__( 'The geolocation service failed or was disabled.', 'posterno' ),
			'geolocationNotSupported' => esc_html__( 'Your browser doesn\'t support geolocation.', 'posterno' ),
		],
	];
	wp_localize_script( 'pno-general', 'pno_settings', $js_vars );

}
add_action( 'wp_enqueue_scripts', 'pno_load_frontend_scripts' );
