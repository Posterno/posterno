<?php
/**
 * Registers a custom rest api controller for the options panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The class that register a new rest api controller to handle options storage.
 */
class PNO_Options_Api extends WP_REST_Controller {

	/**
	 * Declared namespace for the api.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * Version of the api.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * All the registered settings that we're going to parse.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Store errors if any.
	 *
	 * @var object
	 */
	protected $errors;

	/**
	 * Get controller started.
	 */
	public function __construct() {

		$this->version   = 'v1';
		$this->settings  = $settings;
		$this->namespace = 'posterno/' . $this->version . '/options';

		$this->errors = new WP_Error();

		add_filter( 'pn_settings_sanitize_text', array( $this, 'sanitize_text_field' ), 3, 10 );
		add_filter( 'pn_settings_sanitize_textarea', array( $this, 'sanitize_textarea_field' ), 3, 10 );
		add_filter( 'pn_settings_sanitize_radio', array( $this, 'sanitize_text_field' ), 3, 10 );
		add_filter( 'pn_settings_sanitize_select', array( $this, 'sanitize_text_field' ), 3, 10 );
		add_filter( 'pn_settings_sanitize_checkbox', array( $this, 'sanitize_checkbox_field' ), 3, 10 );
		add_filter( 'pn_settings_sanitize_multiselect', array( $this, 'sanitize_multiselect_field' ), 3, 10 );
		add_filter( 'pn_settings_sanitize_multicheckbox', array( $this, 'sanitize_multicheckbox_field' ), 3, 10 );

	}

	/**
	 * Register new routes for the options kit panel.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/save', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_options' ),
				'permission_callback' => array( $this, 'get_options_permission' ),
			),
		) );
	}

	/**
	 * Detect if the user can submit options.
	 *
	 * @return void
	 */
	public function get_options_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'Posterno: Permission Denied.' ), array( 'status' => 401 ) );
		}
		return true;
	}

	/**
	 * Save options to the database. Sanitize them first.
	 *
	 * @param WP_REST_Request $request
	 * @return void
	 */
	public function save_options( WP_REST_Request $request ) {

		$registered_settings = pno_get_registered_settings();
		$settings_received   = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? $_POST['settings']: false;
		$data_to_save        = [];

		if ( is_array( $registered_settings ) && ! empty( $registered_settings ) ) {
			foreach ( $registered_settings as $setting_section ) {
				foreach ( $setting_section as $setting_id => $setting ) {

					// Skip if no setting type.
					if ( ! $setting['type'] ) {
						continue;
					}

					// Skip if the ID doesn't exist in the data received.
					if ( ! array_key_exists( $setting_id, $settings_received ) ) {
						continue;
					}

					// Sanitize the input.
					$setting_type = $setting['type'];
					$value        = $settings_received[ $setting_id ];
					$output       = apply_filters( 'pn_settings_sanitize_' . $setting_type, $value, $this->errors, $setting );
					$output       = apply_filters( 'pn_settings_sanitize_' . $setting_id, $output, $this->errors, $setting );

					if ( $setting_type == 'checkbox' && $output === false ) {
						continue;
					}

					// Add the option to the list of ones that we need to save.
					if ( ! empty( $output ) && ! is_wp_error( $output ) ) {
						$data_to_save[ $setting_id ] = $output;
					}
				}
			}
		}

		if ( ! empty( $this->errors->get_error_codes() ) ) {
			return new WP_REST_Response( $this->errors, 422 );
		}

		update_option( 'pno_settings', $data_to_save );

		return rest_ensure_response( $data_to_save );

	}

	/**
	 * Sanitize the text field.
	 *
	 * @param string $input
	 * @param object $errors
	 * @param array $setting
	 * @return string
	 */
	public function sanitize_text_field( $input, $errors, $setting ) {
		return trim( wp_strip_all_tags( $input, true ) );
	}

	/**
	 * Sanitize textarea field.
	 *
	 * @param string $input
	 * @param object $errors
	 * @param array $setting
	 * @return string
	 */
	public function sanitize_textarea_field( $input, $errors, $setting ) {
		return stripslashes( wp_kses_post( $input ) );
	}

	/**
	 * Sanitize the checkbox field.
	 *
	 * @param string $input
	 * @param object $errors
	 * @param array $setting
	 * @return void
	 */
	public function sanitize_checkbox_field( $input, $errors, $setting ) {
		$pass = false;
		if ( $input == 'true' ) {
			$pass = true;
		}
		return $pass;
	}

	/**
	 * Sanitize multiselect and multicheck field.
	 *
	 * @param mixed $input
	 * @param object $errors
	 * @param array $setting
	 * @return array
	 */
	public function sanitize_multiselect_field( $input, $errors, $setting ) {

		$new_input = array();

		if ( isset( $setting['multiple'] ) && $setting['multiple'] === true ) {
			foreach ( $input as $value ) {

				$saved_label = sanitize_text_field( $value['label'] );
				$saved_value = sanitize_text_field( $value['value'] );

				$new_input[] = [
					'label' => $saved_label,
					'value' => $saved_value,
				];

			}
		} else {

			$saved_label = sanitize_text_field( $input['label'] );
			$saved_value = sanitize_text_field( $input['value'] );

			$new_input['label'] = $saved_label;
			$new_input['value'] = $saved_value;
		}

		return $new_input;
	}

	/**
	 * Sanitize the multicheckbox field.
	 *
	 * @param string $input
	 * @param object $errors
	 * @param array $setting
	 * @return void
	 */
	public function sanitize_multicheckbox_field( $input, $errors, $setting ) {

		$new_input = [];

		if ( is_array( $input ) && ! empty( $input ) ) {
			foreach ( $input as $key => $value ) {
				$new_input[ sanitize_key( $key ) ] = sanitize_text_field( $value );
			}
		}

		return $new_input;

	}

}
