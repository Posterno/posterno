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

	global $pno_settings_page, $pno_custom_fields_page;

	$pno_custom_fields_page = add_submenu_page( 'edit.php?post_type=listings', __( 'Custom fields' ), __( 'Custom fields' ), 'manage_options', 'posterno-custom-fields', 'pno_custom_fields_page' );
	$pno_settings_page      = add_submenu_page( 'edit.php?post_type=listings', __( 'Posterno Settings' ), __( 'Settings' ), 'manage_options', 'posterno-settings', 'pno_options_page' );
}
add_action( 'admin_menu', 'pno_add_admin_pages', 10 );
