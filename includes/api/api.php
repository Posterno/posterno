<?php
/**
 * Hook into the WP's rest api and register custom controllers.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Hook into the rest api and register our controllers.
 *
 * @return void
 */
function pno_register_rest_controllers() {

	// Include rest controller for main public api.
	require_once PNO_PLUGIN_DIR . 'includes/abstracts/abstract-pno-rest-controller.php';

	require_once PNO_PLUGIN_DIR . 'includes/api/class-pno-profile-fields-api.php';

	$profile_fields_editor = new PNO_Profile_Fields_Api();
	$profile_fields_editor->register_routes();

	require_once PNO_PLUGIN_DIR . 'includes/api/class-pno-registration-fields-api.php';

	$registration_fields = new PNO_Registration_Fields_Api();
	$registration_fields->register_routes();

	require_once PNO_PLUGIN_DIR . 'includes/api/class-pno-listings-fields-api.php';

	$listings_fields = new PNO_Listings_Fields_Api();
	$listings_fields->register_routes();

}
add_action( 'rest_api_init', 'pno_register_rest_controllers' );
