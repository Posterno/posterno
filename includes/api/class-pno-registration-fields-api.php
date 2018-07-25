<?php
/**
 * Registers a custom rest api controller for the registration fields.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The class that register a new rest api controller to handle registration fields.
 */
class PNO_Registration_Fields_Api extends WP_REST_Controller {

	/**
	 * WP REST API namespace/version.
	 *
	 * @var string
	 */
	protected $namespace = 'posterno/v1/custom-fields';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'registration';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'pno_signup_fields';

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base, array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Detect if the user can do stuff.
	 *
	 * @return mixed
	 */
	public function get_items_permissions_check( $request ) {
		return true;
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'posterno_rest_cannot_view', esc_html__( 'Sorry, you cannot list resources.' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Get registration fields.
	 *
	 * @return void
	 */
	public function get_items( $request ) {

		/*
		$args  = array(
			'post_per_page' => 5,
		);
		$posts = get_posts( $args );

		$data = array();

		if ( empty( $posts ) ) {
			return rest_ensure_response( $data );
		}

		foreach ( $posts as $post ) {
			$response = $this->prepare_item_for_response( $post, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}*/

		return rest_ensure_response( $data );

	}

	/**
	 * Matches the post data to the schema we want.
	 *
	 * @param WP_Post $post The comment object whose response is being prepared.
	 */
	public function prepare_item_for_response( $post, $request ) {

		$post_data = array();

		$schema = $this->get_item_schema();

		// We are also renaming the fields to more understandable names.
		if ( isset( $schema['properties']['id'] ) ) {
			$post_data['id'] = (int) $post->ID;
		}

		if ( isset( $schema['properties']['name'] ) ) {
			$post_data['title'] = 'whatever';
		}

		return rest_ensure_response( $post_data );
	}

	/**
	 * Get the registration field schema, conforming to JSON Schema.
	 *
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			'properties' => array(
				'id'   => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name' => array(
					'description' => __( 'The name for the object.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $schema;

	}

}
