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

	$js_dir  = PNO_PLUGIN_URL . 'assets/js/';
	$css_dir = PNO_PLUGIN_URL . 'assets/css/';
	$version = PNO_VERSION;

	wp_register_style( 'pno-logo', $css_dir . 'posterno-font.css', array(), $version );
	wp_enqueue_style( 'pno-logo' );

}
add_action( 'admin_enqueue_scripts', 'pno_load_admin_scripts', 100 );
