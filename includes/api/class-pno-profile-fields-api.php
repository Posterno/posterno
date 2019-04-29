<?php
/**
 * Registers a custom rest api controller for the profile fields editor in the admin panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that register a new rest api controller to handle profile fields.
 */
class PNO_Profile_Fields_Api extends PNO_REST_Controller {

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
	protected $rest_base = 'profile';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'pno_users_fields';

	/**
	 * Register new routes for the custom fields editor.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array( $this->get_collection_params() ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array_merge(
						$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
						array(
							'name' => array(
								'description' => __( 'Field name.', 'posterno' ),
								'required'    => true,
								'type'        => 'string',
							),
							'type' => array(
								'description' => __( 'Field type.', 'posterno' ),
								'required'    => true,
								'type'        => 'string',
							),
						)
					),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the resource.', 'posterno' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/update-priority',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_priority' ),
					'permission_callback' => array( $this, 'batch_items_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

	}

	/**
	 * Get registration fields.
	 *
	 * @return void
	 */
	public function get_items( $request ) {

		$fields = [];
		$data   = [];

		$fields_query = new PNO\Database\Queries\Profile_Fields( [ 'number' => 100 ] );

		if ( ! empty( $fields_query->items ) && is_array( $fields_query->items ) ) {
			$fields = $fields_query->items;
		}

		if ( empty( $fields ) ) {
			return rest_ensure_response( $data );
		}

		if ( is_array( $fields ) && ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$response = $this->prepare_item_for_response( $field, $request );

				if ( isset( $request['exclude'] ) && ! is_array( $request['exclude'] ) ) {
					return new WP_Error( 'posterno_rest_cannot_exclude', __( 'Cannot exclude fields. Parameter must be an array.', 'posterno' ), array( 'status' => 400 ) );
				}

				if ( isset( $request['exclude'] ) && is_array( $request['exclude'] ) && isset( $response->data['meta'] ) && in_array( $response->data['meta'], $request['exclude'] ) ) {
					continue;
				}

				$data[] = $this->prepare_response_for_collection( $response );
			}
		}

		return rest_ensure_response( $data );

	}

	/**
	 * Matches the post data to the schema we want.
	 *
	 * @param WP_Post $post The comment object whose response is being prepared.
	 */
	public function prepare_item_for_response( $post, $request ) {

		$post_data = array();
		$schema    = $this->get_item_schema();

		$field = $post;

		if ( isset( $schema['properties']['id'] ) ) {
			$post_data['id'] = (int) $field->getPostID();
		}
		if ( isset( $schema['properties']['name'] ) ) {
			$post_data['name'] = $field->getTitle();
		}
		if ( isset( $schema['properties']['priority'] ) ) {
			$post_data['priority'] = (int) $field->getPriority();
		}
		if ( isset( $schema['properties']['default'] ) ) {
			$post_data['default'] = (bool) ! $field->canDelete();
		}
		if ( isset( $schema['properties']['type'] ) ) {
			$post_data['type']          = $field->getType();
			$post_data['type_nicename'] = $field->getTypeNicename();
		}
		if ( isset( $schema['properties']['required'] ) ) {
			$post_data['required'] = (bool) $field->isRequired();
		}

		$response = rest_ensure_response( $post_data );
		$response->add_links( $this->prepare_links( $post, $request ) );

		return rest_ensure_response( $response );

	}

	/**
	 * Prepare links for the request.
	 *
	 * @param PNO_Profile_Field $object  Object data.
	 * @param WP_REST_Request   $request Request object.
	 * @return array                   Links for the given post.
	 */
	protected function prepare_links( $object, $request ) {
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->getPostID() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( current_user_can( 'manage_options' ) ) {
			$admin_url = admin_url( 'post.php' );
			$admin_url = add_query_arg(
				[
					'post'   => $object->getPostID(),
					'action' => 'edit',
				],
				$admin_url
			);

			$links['admin'] = array(
				'href' => $admin_url,
			);
		}

		return $links;
	}

	/**
	 * Create a profile field.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function create_item( $request ) {

		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( 'posterno_rest_cannot_create_exists', __( 'Cannot create existing field.', 'posterno' ), array( 'status' => 400 ) );
		}

		$field_name     = isset( $request['name'] ) ? sanitize_text_field( $request['name'] ) : false;
		$field_priority = isset( $_POST['priority'] ) && ! empty( $_POST['priority'] ) ? absint( $_POST['priority'] ) : false;

		$registered_field_types = pno_get_registered_field_types();
		$field_type             = isset( $request['type'] ) && isset( $registered_field_types[ $request['type'] ] ) ? sanitize_text_field( $request['type'] ) : false;

		if ( ! $field_name ) {
			return new WP_REST_Response( esc_html__( 'Please enter a name for the new field.', 'posterno' ), 422 );
		}

		if ( ! $field_type ) {
			return new WP_REST_Response( esc_html__( 'Invalid field type.', 'posterno' ), 422 );
		}

		$new_field = \PNO\Entities\Field\Profile::create(
			[
				'name'     => $field_name,
				'priority' => $field_priority,
				'type'     => $field_type,
			]
		);

		if ( is_wp_error( $new_field ) ) {
			return new WP_REST_Response( $new_field->get_error_message(), 422 );
		}

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $new_field, $request );
		$response = rest_ensure_response( $response );

		return $response;

	}

	/**
	 * Delete the selected profile field.
	 *
	 * @param array $request
	 * @return boolean
	 */
	public function delete_item( $request ) {

		$field_id = isset( $request['id'] ) && ! empty( $request['id'] ) ? absint( $request['id'] ) : false;

		if ( ! $field_id ) {
			return new WP_REST_Response( esc_html__( 'Something went wrong while deleting the field, please contact support.', 'posterno' ), 422 );
		}

		$field = new PNO\Field\Profile( $field_id );

		if ( $field instanceof PNO\Field\Profile && $field->get_post_id() > 0 ) {

			if ( $field->can_delete() ) {
				$field->delete();
			} else {
				return new WP_REST_Response( esc_html__( 'Something went wrong while deleting the field, please contact support.', 'posterno' ), 422 );
			}
		}

		return rest_ensure_response( $field_id );

	}

	/**
	 * Updates the priority for each field.
	 *
	 * @param array $request
	 * @return mixed
	 */
	public function update_priority( $request ) {

		$fields = isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ? $_POST['fields'] : false;

		if ( ! $fields ) {
			return new WP_REST_Response( esc_html__( 'Something went wrong while updating the order of the fields, please contact support.', 'posterno' ), 422 );
		}

		foreach ( $fields as $key => $field ) {
			$field_id = isset( $field['id'] ) ? absint( $field['id'] ) : false;
			if ( $field_id ) {
				$field = new PNO\Field\Profile( $field_id );
				$field->update_priority( absint( $key ) + 1 );
			}
		}

		return rest_ensure_response( $fields );

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
				'id'                 => array(
					'description' => __( 'Unique identifier for the object.', 'posterno' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'               => array(
					'description' => __( 'The name for the profile field.', 'posterno' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'label'              => array(
					'description' => __( 'The optional label for the profile field used within forms.', 'posterno' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'meta'               => array(
					'description' => __( 'The user meta key for the field used to store users information.', 'posterno' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'priority'           => array(
					'description' => __( 'The priority number assigned to the field used to defined the order within forms.', 'posterno' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'default'            => array(
					'description' => __( 'Flag to determine if the field is a default field.', 'posterno' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'type'               => array(
					'description' => __( 'Field type.', 'posterno' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'description'        => array(
					'description' => __( 'Field description.', 'posterno' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'placeholder'        => array(
					'description' => __( 'Field placeholder.', 'posterno' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'required'           => array(
					'description' => __( 'Flag to determine if the field is required when displayed within forms.', 'posterno' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'read_only'          => array(
					'description' => __( 'Flag to determine if the field is read-only when displayed within forms.', 'posterno' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'admin_only'         => array(
					'description' => __( 'Flag to determine if the field is admin only and hidden from forms.', 'posterno' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'selectable_options' => array(
					'description' => __( 'Holds selectable options if the field needs them. Example: dropdown or multiselect fields.', 'posterno' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
					),
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

		return $schema;

	}

	/**
	 * Retrieves the query params for the models collection.
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['exclude'] = array(
			'description' => esc_html__( 'Limit results by excluding fields by meta key.', 'posterno' ),
			'type'        => 'array',
			'required'    => false,
		);

		return $query_params;

	}

}
