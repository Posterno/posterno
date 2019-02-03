<?php
/**
 * Scripts and styles registration.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

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

	wp_register_style( 'pno-logo', $css_dir . 'admin-font.min.css', array(), $version );
	wp_register_style( 'pno-editors-styling', $css_dir . 'admin-custom-fields-editor.min.css', [], $version );
	wp_register_style( 'pno-editors-styling-post-type', $css_dir . 'admin-custom-fields-cpt.min.css', [], $version );
	wp_register_style( 'pno-getting-started', $css_dir . 'admin-getting-started.min.css', [], $version );

	wp_enqueue_style( 'pno-logo' );

	if ( defined( 'PNO_VUE_DEV' ) && PNO_VUE_DEV === true ) {

		// Register the custom fields page scripts.
		wp_register_script( 'pno-custom-fields-page', 'http://localhost:8080/custom-fields-editor.js', [], $version, true );
		wp_register_script( 'pno-registration-form-editor', 'http://localhost:8080/registration-form-editor.js', [], $version, true );
		wp_register_script( 'pno-profile-fields-editor', 'http://localhost:8080/profile-fields.js', [], $version, true );
		wp_register_script( 'pno-listings-fields-editor', 'http://localhost:8080/listings-fields-editor.js', [], $version, true );

	} else {

	}

	// Load scripts for the registration form editor page.
	if ( $screen->id === 'users_page_posterno-custom-registration-form' ) {
		wp_enqueue_style( 'pno-editors-styling' );
		wp_enqueue_script( 'pno-registration-form-editor' );
		wp_localize_script( 'pno-registration-form-editor', 'pno_fields_editor', pno_get_custom_fields_editor_js_vars() );
	}

	// Load scritps for the profile fields editor page.
	if ( $screen->id === 'users_page_posterno-custom-profile-fields' ) {
		wp_enqueue_style( 'pno-editors-styling' );
		wp_enqueue_script( 'pno-profile-fields-editor' );
		wp_localize_script( 'pno-profile-fields-editor', 'pno_fields_editor', pno_get_custom_fields_editor_js_vars() );
	}

	if ( $screen->id === 'listings_page_posterno-custom-listings-fields' ) {
		wp_enqueue_style( 'pno-editors-styling' );
		wp_enqueue_script( 'pno-listings-fields-editor' );
		wp_localize_script( 'pno-listings-fields-editor', 'pno_fields_editor', pno_get_custom_fields_editor_js_vars() );
	}

	if ( $screen->id === 'pno_users_fields' ) {
		wp_enqueue_style( 'pno-editors-styling-post-type' );
		wp_enqueue_script( 'pnocf-validation', PNO_PLUGIN_URL . '/assets/js/admin/admin-profile-fields-settings-validation.min.js', [], $version, true );
		wp_localize_script( 'pnocf-validation', 'pno_user_cf', pno_get_users_custom_fields_page_vars() );
	}

	if ( $screen->id === 'pno_listings_fields' ) {
		wp_enqueue_style( 'pno-editors-styling-post-type' );
		wp_enqueue_script( 'pnocf-validation', PNO_PLUGIN_URL . '/assets/js/admin/admin-listings-fields-settings-validation.min.js', [], $version, true );
		wp_localize_script( 'pnocf-validation', 'pno_listing_cf', pno_get_listing_custom_fields_page_vars() );
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

	if ( in_array( $screen->id, $admin_style_screens ) ) {
		wp_enqueue_style( 'pno-admin-style', PNO_PLUGIN_URL . '/assets/css/admin/admin-listings.min.css', [], $version );
	}

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
	wp_register_style( 'pno-bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css', [], $version );
	wp_register_style( 'pno-fa', 'https://use.fontawesome.com/releases/v5.2.0/css/all.css', [], $version );
	wp_register_style( 'pno-select2-style', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css', false, $version );
	wp_register_style( 'pno-flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], $version );
	wp_register_style( 'pno', PNO_PLUGIN_URL . 'assets/css/frontend/posterno.min.css', [], $version );

	wp_register_script( 'pno-bootstrap-script', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js', [ 'jquery' ], $version, true );
	wp_register_script( 'pno-bootstrap-script-popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js', [ 'jquery' ], $version, true );
	wp_register_script( 'pno-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js', array( 'jquery' ), $version, true );
	wp_register_script( 'pno-flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', false, $version, true );
	wp_register_script( 'pno-googlemap', 'https://maps.googleapis.com/maps/api/js?key=' . pno_get_option( 'google_maps_api_key' ), false, $version, true );
	wp_register_script( 'pno-general', PNO_PLUGIN_URL . 'assets/js/frontend/posterno.min.js', array( 'jquery' ), $version, true );

	// Register vuejs related scripts.
	if ( defined( 'PNO_VUE_DEV' ) && PNO_VUE_DEV === true ) {
		wp_register_script( 'pno-vuejs', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js', [], $version, true );
		wp_register_script( 'pno-vue-listing-submission-form', 'http://localhost:8080/listing-submission-form.js', [], $version, true );
	} else {
		wp_register_script( 'pno-vuejs', 'https://cdn.jsdelivr.net/npm/vue', [], $version, true );
	}

	wp_enqueue_script( 'jquery' );

	// Load the required style only if enabled.
	if ( pno_get_option( 'bootstrap_style' ) ) {
		wp_enqueue_style( 'pno-bootstrap' );
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

	// Load the maps api within the single listing page.
	if ( is_singular( 'listings' ) ) {
		wp_enqueue_script( 'pno-googlemap' );
	}

	// Register pno's own stylesheet.
	wp_enqueue_style( 'pno' );
	wp_enqueue_script( 'pno-general' );

	$js_vars = [
		'bootstrap'                        => (bool) pno_get_option( 'bootstrap_style' ),
		'mapProvider'                      => pno_get_option( 'map_provider', 'googlemaps' ),
		'googleMapsApiKey'                 => pno_get_option( 'google_maps_api_key' ),
		'startingLatitude'                 => pno_get_option( 'map_starting_lat', '40.7484405' ),
		'startingLongitude'                => pno_get_option( 'map_starting_lng', '-73.9944191' ),
		'mapZoom'                          => pno_get_option( 'map_zoom', '12' ),
		'internal_links_new_tab'           => (bool) pno_get_option( 'listing_open_new_tab', false ),
		'internal_links_new_tab_selectors' => pno_get_internal_listing_links_selectors(),
		'external_links_new_tab'           => (bool) pno_get_option( 'listing_external_open_new_tab', false ),
		'external_links_rel_attributes'    => (bool) pno_get_option( 'listing_external_rel_attributes', false ),
		'external_links_new_tab_selectors' => pno_get_external_listing_links_selectors(),
		'labels'                           => [
			'addressNotFound'         => esc_html__( 'Address not found, please try again.' ),
			'geolocationFailed'       => esc_html__( 'The geolocation service failed or was disabled.' ),
			'geolocationNotSupported' => esc_html__( 'Your browser doesn\'t support geolocation.' ),
		],
	];
	wp_localize_script( 'pno-general', 'pno_settings', $js_vars );

	// Load vuejs scripts within the listing submission/editing page.
	if ( is_page( pno_get_listing_submission_page_id() ) || is_page( pno_get_listing_editing_page_id() ) ) {
		wp_enqueue_style( 'pno-select2-style' );
		wp_enqueue_style( 'pno-flatpickr' );
		wp_enqueue_script( 'pno-select2' );
		wp_enqueue_script( 'pno-flatpickr' );
		wp_enqueue_script( 'pno-vuejs' );
		wp_enqueue_script( 'pno-vue-listing-submission-form' );
	}

	// Js settings for the submission form.
	wp_localize_script( 'pno-vue-listing-submission-form', 'pno_submission', pno_get_listings_submission_form_js_vars() );

}
add_action( 'wp_enqueue_scripts', 'pno_load_frontend_scripts' );
