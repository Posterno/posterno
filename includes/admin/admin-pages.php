<?php
/**
 * Handles manipulation and registration of admin pages.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register admin pages for the plugin.
 *
 * @return void
 */
function pno_add_admin_pages() {

	global $pno_settings_page, $pno_custom_fields_page, $pno_registration_fields_page, $pno_profile_fields_page, $pno_listings_fields_page;

	$pno_profile_fields_page      = add_users_page( __( 'Custom profile fields' ), __( 'Custom fields' ), 'manage_options', 'posterno-custom-profile-fields', 'pno_custom_profile_fields_page' );
	$pno_registration_fields_page = add_users_page( __( 'Customize registration form' ), __( 'Registration form' ), 'manage_options', 'posterno-custom-registration-form', 'pno_custom_registration_fields_page' );
	$pno_listings_fields_page     = add_submenu_page( 'edit.php?post_type=listings', __( 'Customize listings fields' ), __( 'Custom fields' ), 'manage_options', 'posterno-custom-listings-fields', 'pno_custom_listings_fields_page' );
	$pno_settings_page            = add_options_page( __( 'Posterno Settings' ), __( 'Posterno' ), 'manage_options', 'posterno-settings', 'pno_options_page' );

}
add_action( 'admin_menu', 'pno_add_admin_pages', 10 );

/**
 * Remove taxonomies and other menu items from the listings menu in the admin panel.
 *
 * @return void
 */
function pno_admin_remove_submenus() {

	$taxonomies = get_object_taxonomies( 'listings' );

	if ( empty( $taxonomies ) ) {
		return;
	}

	foreach ( $taxonomies as $taxonomy ) {
		remove_submenu_page( 'edit.php?post_type=listings', 'edit-tags.php?taxonomy=' . $taxonomy . '&amp;post_type=listings' );
	}

	remove_submenu_page( 'edit.php?post_type=pno_emails', 'edit-tags.php?taxonomy=pno-email-type&amp;post_type=pno_emails' );

}
add_action( 'admin_menu', 'pno_admin_remove_submenus', 999 );
