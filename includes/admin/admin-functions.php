<?php
/**
 * All the functions that are only used within the admin panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve pages from the database and cache them as transient.
 *
 * @return array
 */
function pno_get_pages( $force = false ) {
	$pages = [];
	if ( ( ! isset( $_GET['page'] ) || 'posterno-settings' != $_GET['page'] ) && ! $force ) {
		return $pages;
	}
	$transient = get_transient( 'pno_get_pages' );
	if ( $transient ) {
		$pages = $transient;
	} else {
		$available_pages = get_pages();
		if ( ! empty( $available_pages ) ) {
			foreach ( $available_pages as $page ) {
				$pages[] = array(
					'value' => $page->ID,
					'label' => $page->post_title,
				);
			}
			set_transient( 'pno_get_pages', $pages, DAY_IN_SECONDS );
		}
	}
	return $pages;
}

/**
 * Load tinymce plugin
 *
 * @access public
 * @since  0.1.0
 * @return $plugin_array
*/
function pno_shortcodes_add_tinymce_plugin( $plugin_array ) {

	$plugin_array['pno_shortcodes_mce_button'] = apply_filters( 'pno_shortcodes_tinymce_js_file_url', PNO_PLUGIN_URL . '/includes/admin/tinymce/js/pno-tinymce.js' );

	return $plugin_array;

}

/**
 * Load tinymce button
 *
 * @access public
 * @since  1.0.0
 * @return $buttons
*/
function pno_shortcodes_register_mce_button( $buttons ) {

	array_push( $buttons, 'pno_shortcodes_mce_button' );

	return $buttons;

}
