<?php
/**
 * Handles settings registration for posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not.
 *
 * @since 0.1.0
 * @global $pno_options Array of all the Posterno's options
 * @return mixed
 */
function pno_get_option( $key = '', $default = false ) {
	global $pno_options;
	$value = ! empty( $pno_options[ $key ] )
		? $pno_options[ $key ]
		: $default;

	/**
	 * Filters the retrieval of an option.
	 *
	 * @since 0.1.0
	 * @param mixed $value the original value.
	 * @param string $key the key of the option being retrieved.
	 * @param mixed $default default value if nothing is found.
	 */
	$value = apply_filters( 'pno_get_option', $value, $key, $default );
	return apply_filters( 'pno_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option
 *
 * Updates an pno setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the pno_options array.
 *
 * @since 0.1.0
 *
 * @param string          $key         The Key to update
 * @param string|bool|int $value       The value to set the key to
 * @global                $pno_options Array of all the Posterno Options
 * @return boolean True if updated, false if not.
 */
function pno_update_option( $key = '', $value = false ) {
	global $pno_options;

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = pno_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings.
	$options = get_option( 'pno_settings' );

	/**
	 * Filter the final value of an option before being saved into the database.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $value the value about to be saved.
	 * @param string $key the key of the option that is being saved.
	 */
	$value = apply_filters( 'pno_update_option', $value, $key );

	// Next let's try to update the value.
	$options[ $key ] = $value;
	$did_update      = update_option( 'pno_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		$pno_options[ $key ] = $value;
	}
	return $did_update;
}

/**
 * Remove an option
 * Removes a setting value in both the db and the global variable.
 *
 * @since 0.1.0
 *
 * @param string $key         The Key to delete
 * @global       $pno_options Array of all the Posterno Options
 * @return boolean True if removed, false if not.
 */
function pno_delete_option( $key = '' ) {

	global $pno_options;

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings.
	$options = get_option( 'pno_settings' );

	// Next let's try to update the value.
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	// Remove this option from the global Posterno settings to the array_merge in pno_settings_sanitize() doesn't re-add it.
	if ( isset( $pno_options[ $key ] ) ) {
		unset( $pno_options[ $key ] );
	}

	$did_update = update_option( 'pno_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		$pno_options = $options;
	}
	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array Posterno settings
 */
function pno_get_settings() {

	// Get the option key.
	$settings = get_option( 'pno_settings' );

	/**
	 * Filter retrieval of all options.
	 *
	 * @since 0.1.0
	 * @param mixed $settings the list of options stored into the database.
	 */
	return apply_filters( 'pno_get_settings', $settings );

}

/**
 * Handles registration of translatable strings for the javascript powered settings panel
 * together with any other string needed for the panel.
 *
 * @return array
 */
function pno_get_settings_page_vars() {

	$vars = [
		'plugin_url'          => PNO_PLUGIN_URL,
		'settings_tabs'       => pno_get_registered_settings_tabs(),
		'settings_sections'   => pno_get_registered_settings_tabs_sections(),
		'registered_settings' => pno_get_registered_settings(),
		'vuejs_model'         => pno_prepare_registered_settings_vue_model(),
		'labels'              => [
			'page_title'     => esc_html__( 'Posterno Settings' ),
			'save'           => esc_html__( 'Save changes' ),
			'read_docs'      => esc_html__( 'Documentation' ),
			'settings_saved' => esc_html__( 'Settings successfully saved.' ),
			'addons'         => esc_html__( 'View Addons' ),
			'multiselect'    => [
				'selected' => esc_html__( 'selected' ),
			],
		],
	];

	return $vars;

}

/**
 * Retrieve the list of settings tabs for the options panel.
 *
 * @return array
 */
function pno_get_registered_settings_tabs() {

	$tabs = [
		'general' => esc_html__( 'General' ),
		'another' => 'Testing tab2'
	];

	/**
	 * Allows developers to register or deregister tabs for the
	 * settings panel.
	 *
	 * @since 0.1.0
	 * @param array $tabs
	 */
	return apply_filters( 'pno_registered_settings_tabs', $tabs );

}

/**
 * Retrieve the list of settings subsections for all tabs.
 *
 * @return array
 */
function pno_get_registered_settings_tabs_sections() {

	$sections = [
		'another' => [
			'section1' => 'Sub section testing'
		]
	];

	/**
	 * Allows developers to register or deregister subsections for tabs in the
	 * settings panel.
	 *
	 * @since 0.1.0
	 * @param array $sections
	 */
	return apply_filters( 'pno_registered_settings_tabs_sections', $sections );

}

/**
 * Retrieve the list of registered settings for the admin panel.
 *
 * @return array
 */
function pno_get_registered_settings() {

	$settings = [
		'general' => [
			'setting_id' => [
				'type'        => 'text',
				'label'       => 'Label goes here',
				'description' => 'Description goes here',
				'placeholder' => 'Placeholder',
			],
			'setting_2' => [
				'type'        => 'textarea',
				'label'       => 'Label goes here',
				'description' => 'Description goes here',
				'placeholder' => 'Placeholder',
			],
			'setting_4' => [
				'type'        => 'radio',
				'label'       => 'Label goes here',
				'description' => 'Description goes here',
				'placeholder' => 'Placeholder',
				'options' => [
					'val' => 'label',
					'val2' => 'label2',
				]
			],
			'setting_check' => [
				'type'        => 'checkbox',
				'label'       => 'Label goes here',
				'description' => 'Description goes here',
				'placeholder' => 'Placeholder',
			],
			'setting_m4' => [
				'type'        => 'multicheckbox',
				'label'       => 'Label goes here',
				'description' => 'Description goes here',
				'placeholder' => 'Placeholder',
				'options' => [
					'val' => 'label',
					'val2' => 'label2',
				]
			],

			'setting_m5s' => [
				'type'        => 'multiselect',
				'label'       => 'Label goes here',
				'description' => 'Description goes here',
				'placeholder' => 'Placeholder',
				'options' => [
					[
						'label' => 'Label goes here for testing',
						'value' => 'value1'
					],
					[
						'label' => 'Label goes here for testing ddd',
						'value' => 'value2'
					],
					[
						'label' => 'Label goes here for testing 2',
						'value' => 'value3'
					]
				]
			],
		],
		'section1' => [
			'subsection_field_test' => [
				'type'        => 'text',
				'label'       => 'Subsection field test',
				'description' => 'Description goes here',
				'placeholder' => 'Placeholder',
			],
			'setting_mffff' => [
				'type'        => 'multiselect',
				'label'       => 'Label goes here',
				'description' => 'Description goes here',
				'placeholder' => 'Placeholder',
				'options' => [
					[
						'label' => 'Label goes here for testing',
						'value' => 'value1'
					],
					[
						'label' => 'Label goes here for testing ddd',
						'value' => 'value2'
					],
					[
						'label' => 'Label goes here for testing 2',
						'value' => 'value3'
					]
				]
			],
		]
	];

	/**
	 * Allows developers to register or deregister settings for the admin panel.
	 *
	 * @since 0.1.0
	 * @param array $settings
	 */
	return apply_filters( 'pno_registered_settings', $settings );

}

/**
 * Creates an object that is passed to vuejs, it prepares all
 * registered settings to be read and modified by vuejs.
 * This function also loads the stored settings.
 *
 * @return array
 */
function pno_prepare_registered_settings_vue_model() {

	$model = [];

	$registered_settings = pno_get_registered_settings();

	if ( is_array( $registered_settings ) && ! empty( $registered_settings ) ) {
		foreach ( $registered_settings as $settings_section ) {
			if ( is_array( $settings_section ) && ! empty( $settings_section ) ) {
				foreach ( $settings_section as $option_id => $setting ) {

					$value = ! empty( pno_get_option( $option_id ) ) ? pno_get_option( $option_id ) : false;

					switch ( $setting['type'] ) {
						case 'text':
						case 'textarea':
						case 'select':
						case 'radio':
							$value = empty( $value ) ? '' : $value;
							break;
						case 'checkbox':
							$value = ! empty( $value ) ? true : false;
							break;
						case 'multicheckbox':
							$options = [];
							if ( is_array( $value ) && ! empty( $value ) ) {
								$options = $value;
							}
							$value = $options;
							break;
						case 'multiselect':
							if ( ! is_array( $value ) || is_array( $value ) && empty( $value ) ) {
								$value = [];
							}
							break;
					}

					$model[ $option_id ] = $value;

				}
			}
		}
	}

	/**
	 * Allows developers to modify the data settings model that is sent
	 * to the settings panel for vuejs. The model is what the panel reads as currently
	 * stored settings.
	 *
	 * @since 0.1.0
	 * @param array $model
	 */
	return apply_filters( 'pno_registered_settings', $model );

}
