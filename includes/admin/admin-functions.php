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

	$plugin_array['pno_shortcodes_mce_button'] = apply_filters( 'pno_shortcodes_tinymce_js_file_url', PNO_PLUGIN_URL . 'assets/js/pno-tinymce.min.js' );

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

/**
 * Retrieve the options for the available login methods.
 *
 * @return array
 */
function pno_get_login_methods() {
	return apply_filters(
		'pno_get_login_methods', array(
			'username'       => __( 'Username only' ),
			'email'          => __( 'Email only' ),
			'username_email' => __( 'Username or Email' ),
		)
	);
}

/**
 * Retrieve a list of all user roles and cache them into a transient.
 *
 * @param boolean $force set to true if loading outside the pno settings
 * @param boolean $admin set to true to load the admin role too
 * @return array
 */
function pno_get_roles( $force = false, $admin = false ) {
	$roles = [];
	if ( ( ! isset( $_GET['page'] ) || 'posterno-settings' != $_GET['page'] ) && ! $force ) {
		return $roles;
	}
	$transient = get_transient( 'pno_get_roles' );
	if ( $transient && ! $force ) {
		$roles = $transient;
	} else {
		global $wp_roles;
		$available_roles = $wp_roles->get_names();
		foreach ( $available_roles as $role_id => $role ) {
			if ( $role_id == 'administrator' && ! $admin ) {
				continue;
			}
			$roles[] = array(
				'value' => esc_attr( $role_id ),
				'label' => esc_html( $role ),
			);
		}
		//set_transient( 'pno_get_roles', $roles, DAY_IN_SECONDS );
	}
	return $roles;
}

/**
 * Mark specific field types as "multi options". The custom fields
 * editor will allow generation of options for those field types.
 *
 * @return array
 */
function pno_get_multi_options_field_types() {

	$types = [
		'select',
		'multiselect',
		'multicheckbox',
	];

	return apply_filters( 'pno_multi_options_field_types', $types );

}
