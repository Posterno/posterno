<?php
/**
 * List of functions used during the installation of the plugin.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Runs on plugin install by setting up the post types, custom taxonomies, flushing rewrite rules to initiate the new
 * slugs and also creates the plugin and populates the settings fields for those plugin pages.
 *
 * @param boolean $network_wide whether the plugin is being activated network wide or not.
 * @return void
 */
function posterno_install( $network_wide = false ) {
	global $wpdb;
	if ( is_multisite() && $network_wide ) {
		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {
			switch_to_blog( $blog_id );
			pno_run_install();
			restore_current_blog();
		}
	} else {
		pno_run_install();
	}
}

/**
 * Run the installation process of the plugin.
 *
 * @return void
 */
function pno_run_install() {

	// Setup plugin's components.
	pno_setup_components();
	pno_install_component_database_tables();

	// Setup the post types.
	pno_setup_post_types();
	pno_setup_listings_custom_fields_post_type();
	pno_setup_users_custom_fields_post_type();
	pno_setup_registration_fields_post_type();
	pno_setup_emails_post_type();

	// Setup taxonomies.
	pno_register_listings_taxonomies();
	pno_register_email_taxonomy();

	// Install emails and schemas.
	pno_install_email_types();
	pno_install_default_emails();

	// Install default fields.
	pno_install_listings_fields();
	pno_install_profile_fields( true );
	pno_install_registration_fields();

	// Load default options.
	pno_install_default_settings();

	// Install required pages.
	pno_install_pages();

	// Store plugin installation date.
	add_option( 'pno_activation_date', strtotime( 'now' ) );

	// Add Upgraded From Option.
	$current_version = get_option( 'pno_version' );

	if ( $current_version ) {
		update_option( 'pno_version_upgraded_from', $current_version );
	}

	// Enable registrations on the site.
	update_option( 'users_can_register', true );

	// Clear the permalinks.
	flush_rewrite_rules();

	// Update current version.
	update_option( 'pno_version', PNO_VERSION );

	// Add the transient to redirect.
	set_transient( '_pno_activation_redirect', true, 30 );

}
