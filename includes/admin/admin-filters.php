<?php
/**
 * Registers all the filters for the administration.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add new links to the plugin's action links list.
 *
 * @since 1.0.0
 * @return array
 */
function pno_add_settings_link( $links ) {
	$settings_link = '<a href="' . admin_url( 'edit.php?post_type=listings&page=posterno-settings' ) . '">' . esc_html__( 'Settings' ) . '</a>';
	$docs_link     = '<a href="https://docs.posterno.com/" target="_blank">' . esc_html__( 'Documentation' ) . '</a>';
	$addons_link   = '<a href="https://posterno.com/addons" target="_blank">' . esc_html__( 'Addons' ) . '</a>';
	array_unshift( $links, $settings_link );
	array_unshift( $links, $docs_link );
	array_unshift( $links, $addons_link );
	return $links;
}
add_filter( 'plugin_action_links_' . PNO_PLUGIN_BASE, 'pno_add_settings_link' );
