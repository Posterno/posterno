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

		$fields = [];

		return rest_ensure_response( [ 'test' => 'ddd' ] );

	}

}
