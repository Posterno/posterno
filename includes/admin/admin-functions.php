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
 * Install the registration fields within the database.
 * This function is usually used within the plugin's activation.
 *
 * @return void
 */
function pno_install_registration_fields() {

	// Bail if this was already done.
	if ( get_option( 'pno_registration_fields_installed' ) ) {
		return;
	}

	$registered_fields = pno_get_registration_fields();

	if ( is_array( $registered_fields ) ) {
		if ( isset( $registered_fields['robo'] ) ) {
			unset( $registered_fields['robo'] );
		}
		if ( isset( $registered_fields['role'] ) ) {
			unset( $registered_fields['role'] );
		}
		if ( isset( $registered_fields['terms'] ) ) {
			unset( $registered_fields['terms'] );
		}
		if ( isset( $registered_fields['privacy'] ) ) {
			unset( $registered_fields['privacy'] );
		}
	}

	foreach ( $registered_fields as $key => $field ) {

		$new_field = [
			'post_type'   => 'pno_signup_fields',
			'post_title'  => $field['label'],
			'post_status' => 'publish',
		];

		$field_id = wp_insert_post( $new_field );

		if ( ! is_wp_error( $field_id ) ) {

			// Mark the registration field as a default field.
			if ( pno_is_default_profile_field( $key ) ) {
				carbon_set_post_meta( $field_id, 'field_is_default', $key );
			}

			// Setup the priority of this field.
			if ( isset( $field['priority'] ) ) {
				carbon_set_post_meta( $field_id, 'field_priority', $field['priority'] );
			}
		}
	}

}

function testme() {

	if ( isset( $_GET['testme'] ) ) {
		pno_install_registration_fields();
		wp_die();
	}

}
add_action( 'admin_init', 'testme' );
