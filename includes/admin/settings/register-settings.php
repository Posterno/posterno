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
				'selected' => esc_html__( 'Selected' ),
				'deselected' => esc_html__( 'Click to deselect' )
			],
		],
		'nonce'               => wp_create_nonce( 'wp_rest' ),
		'rest'                => esc_url_raw( rest_url() ),
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
		'general'  => esc_html__( 'General' ),
		'accounts' => esc_html__( 'Accounts' ),
		'emails'   => esc_html__( 'Emails' ),
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
		'general'  => [
			'pages' => esc_html__( 'Pages setup' ),
			'misc'  => esc_html__( 'Misc settings' ),
			'theme' => esc_html__( 'Theme' ),
		],
		'accounts' => [
			'login'        => esc_html__( 'Login' ),
			'registration' => esc_html__( 'Registration' ),
			'redirects'    => esc_html__( 'Redirects' ),
		],
		'emails' => [
			'emails_settings' => esc_html__( 'Configuration' ),
			'emails_test'     => esc_html__( 'Test Emails' )
		],
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
		'pages'        => [
			'login_page'        => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'Login page' ),
				'description' => esc_html__( 'Select the page where you have added the login form shortcode.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
			'password_page'     => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'Password recovery page' ),
				'description' => esc_html__( 'Select the page where you have added the password recovery form shortcode.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
			'registration_page' => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'Registration page' ),
				'description' => esc_html__( 'Select the page where you have added the registration form shortcode.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
			'dashboard_page'    => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'Dashboard page' ),
				'description' => esc_html__( 'Select the page where you have added the dashboard shortcode.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
			'profile_page'      => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'Public profile page' ),
				'description' => esc_html__( 'Select the page where you have added the profile shortcode.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
		],
		'misc'         => [
			'lock_wp_login' => [
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Lock access to wp-login.php:' ),
				'description' => esc_html__( 'Enable to lock access to wp-login.php. Users will be redirected to the Posterno login page.' ),
			],
		],
		'theme'        => [
			'bootstrap_style'  => [
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Include Bootstrap css' ),
				'description' => esc_html__( 'Posterno uses bootstrap 4 for styling all of the elements. Disable these options if your theme already makes use of bootstrap.' ),
			],
			'bootstrap_script' => [
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Include Bootstrap scripts' ),
				'description' => esc_html__( 'Posterno uses bootstrap 4 for styling all of the elements. Disable these options if your theme already makes use of bootstrap.' ),
			],
		],
		'login'        => [
			'login_method' => [
				'type'    => 'select',
				'label'   => __( 'Allow users to login with:' ),
				'options' => pno_get_login_methods(),
			],
		],
		'registration' => [
			'login_after_registration' => [
				'label'       => __( 'Login after registration:' ),
				'description' => __( 'Enable this option to automatically authenticate users after registration.' ),
				'type'        => 'checkbox',
			],
			'strong_passwords'         => [
				'label'       => __( 'Require strong passwords:' ),
				'description' => __( 'Enable this option to require strong passwords during registration.' ),
				'type'        => 'checkbox',
			],
			'enable_terms'             => [
				'label'       => __( 'Enable terms & conditions:' ),
				'description' => __( 'Enable to force users to agree to your terms before registering an account.' ),
				'type'        => 'checkbox',
			],
			'terms_page'               => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'Terms Page:' ),
				'description' => esc_html__( 'Select the page that contains your terms.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
		],
		'redirects'    => [
			'login_redirect'        => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'After login' ),
				'description' => esc_html__( 'Select the page where you want to redirect users after they login.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
			'logout_redirect'       => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'After logout' ),
				'description' => esc_html__( 'Select the page where you want to redirect users after they logout. If empty will return to your homepage.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
			'registration_redirect' => [
				'type'        => 'multiselect',
				'label'       => esc_html__( 'After registration' ),
				'description' => esc_html__( 'Select the page where you want to redirect users after they register. If empty a message will be displayed instead.' ),
				'placeholder' => esc_html__( 'Select a page' ),
				'options'     => pno_get_pages(),
				'is_page'     => true,
			],
		],
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

					// ¯\_(ツ)_/¯ vue multiselect needs an int to recognized the stored db selected value
					// ¯\_(ツ)_/¯ so here we force one.
					if (
						$setting['type'] == 'multiselect'
						&& isset( $setting['is_page'] )
						&& $setting['is_page'] === true
						&& isset( $value['value'] ) ) {
						$value['value'] = absint( $value['value'] );
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
