<?php
/**
 * Handles manipulation and registration of admin pages.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
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

	global $pno_registration_fields_page, $pno_profile_fields_page, $pno_listings_fields_page, $pno_tools_page;

	$pno_profile_fields_page      = add_users_page( __( 'Custom profile fields', 'posterno' ), __( 'Custom fields', 'posterno' ), 'manage_options', 'posterno-custom-profile-fields', 'pno_custom_profile_fields_page' );
	$pno_registration_fields_page = add_users_page( __( 'Customize registration form', 'posterno' ), __( 'Registration form', 'posterno' ), 'manage_options', 'posterno-custom-registration-form', 'pno_custom_registration_fields_page' );
	$pno_listings_fields_page     = add_submenu_page( 'edit.php?post_type=listings', __( 'Customize listings fields', 'posterno' ), __( 'Custom fields', 'posterno' ), 'manage_options', 'posterno-custom-listings-fields', 'pno_custom_listings_fields_page' );
	$pno_tools_page               = add_management_page( esc_html__( 'Posterno tools', 'posterno' ), esc_html__( 'Posterno tools', 'posterno' ), 'manage_options', 'posterno-tools', 'pno_tools_page' );

}
add_action( 'admin_menu', 'pno_add_admin_pages', 10 );

/**
 * Remove taxonomies and other menu items from the listings menu in the admin panel.
 *
 * @return void
 */
function pno_admin_remove_submenus() {

	remove_submenu_page( 'edit.php?post_type=pno_emails', 'edit-tags.php?taxonomy=pno-email-type&amp;post_type=pno_emails' );

}
add_action( 'admin_menu', 'pno_admin_remove_submenus', 999 );
