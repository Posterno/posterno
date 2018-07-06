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
	 * Get controller started.
	 */
	public function __construct() {

		$this->version   = 'v1';
		$this->settings  = $settings;
		$this->namespace = 'posterno/' . $this->version . '/options';

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

		return rest_ensure_response( [ 'yo' => 'test' ] );

	}

}
