<?php
/**
 * Registers a custom rest api controller for the custom fields editor in the admin panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The class that register a new rest api controller to handle custom fields.
 */
class PNO_Custom_Fields_Api extends WP_REST_Controller {

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
	 * Get controller started.
	 */
	public function __construct() {
		$this->version   = 'v1';
		$this->namespace = 'posterno/' . $this->version . '/custom-fields';
	}

	/**
	 * Register new routes for the custom fields editor.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/profile', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_profile_fields' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
	}

	/**
	 * Detect if the user can do stuff.
	 *
	 * @return mixed
	 */
	public function check_admin_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'Posterno: Permission Denied.' ), array( 'status' => 401 ) );
		}
		return true;
	}

	/**
	 * Retrieve registered profile fields.
	 *
	 * @param WP_REST_Request $request
	 * @return void
	 */
	public function get_profile_fields( WP_REST_Request $request ) {

		$fields            = [];
		$registered_fields = pno_get_account_fields();

		if ( is_array( $registered_fields ) && ! empty( $registered_fields ) ) {
			foreach ( $registered_fields as $field_key => $field ) {

				$field_in_db = $this->maybe_create_user_field( $field_key );

				$fields[ $field_key ] = [
					'title'    => esc_html( $field['label'] ),
					'type'     => esc_html( $field['type'] ),
					'required' => isset( $field['required'] ) && $field['required'] === true ? true : false,
					'priority' => absint( $field['priority'] ),
					'default'  => $this->is_default_profile_field( $field_key ),
				];

			}
		}

		if ( is_array( $fields ) && ! empty( $fields ) ) {
			uasort( $fields, 'pno_sort_array_by_priority' );
		} else {
			return new WP_REST_Response( esc_html__( 'Something went wrong while retrieving the fields, please contact support.' ), 422 );
		}

		return rest_ensure_response( $fields );

	}

	/**
	 * Determine if a given field type is a default field or not.
	 * Default fields can't be deleted through the UI.
	 *
	 * @param string $key
	 * @return boolean
	 */
	private function is_default_profile_field( $key ) {

		if ( ! $key ) {
			return;
		}

		$default = false;

		switch ( $key ) {
			case 'avatar':
			case 'first_name':
			case 'last_name':
			case 'email':
			case 'website':
			case 'description':
				$default = true;
				break;
		}

		return $default;

	}

	/**
	 * Determine if we're going to create a field into the database or not.
	 * Each field is a post registered within the pno_users_fields post type.
	 *
	 * @param string $field_key
	 * @return void
	 */
	private function maybe_create_user_field( $field_key ) {

		if ( ! $field_key ) {
			return;
		}

		$args = [
			'post_type'      => 'pno_users_fields',
			'posts_per_page' => 1,
			'nopaging'       => true,
			'no_found_rows'  => true,
			'meta_query'     => array(
				'relation'    => 'AND',
				'type_clause' => array(
					'key'   => 'field_type',
					'value' => $field_key,
				),
			),
		];

		$field_query = new WP_Query( $args );

	}

}
