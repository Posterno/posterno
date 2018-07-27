<?php
/**
 * Registers a custom rest api controller for the profile fields editor in the admin panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
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
			$this->namespace, '/' . $this->rest_base, array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array_merge(
						$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ), array(
							'name' => array(
								'description' => __( 'Field name.' ),
								'required'    => true,
								'type'        => 'string',
							),
							'type' => array(
								'description' => __( 'Field type.' ),
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
			$this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the resource.' ),
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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/batch', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'batch_items' ),
				'permission_callback' => array( $this, 'batch_items_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			'schema' => array( $this, 'get_public_batch_schema' ),
		) );

		/*register_rest_route(
			$this->namespace, '/profile', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_profile_fields' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace, '/profile/save-fields-order', array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_profile_fields_order' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace, '/profile/create', array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'create_profile_field' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace, '/profile/delete', array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'delete_profile_field' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);*/
	}

	/**
	 * Get registration fields.
	 *
	 * @return void
	 */
	public function get_items( $request ) {

		$args = [
			'post_type'              => $this->post_type,
			'posts_per_page'         => 100,
			'nopaging'               => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_key'               => '_field_priority',
			'orderby'                => 'meta_value_num',
			'order'                  => 'ASC',
		];

		$fields = new WP_Query( $args );
		$data   = [];

		if ( empty( $fields ) ) {
			return rest_ensure_response( $data );
		}

		if ( is_array( $fields->get_posts() ) && ! empty( $fields->get_posts() ) ) {
			foreach ( $fields->get_posts() as $post ) {
				$response = $this->prepare_item_for_response( $post, $request );
				$data[]   = $this->prepare_response_for_collection( $response );
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

		$field = new PNO_Profile_Field( $post->ID );

		if ( isset( $schema['properties']['id'] ) ) {
			$post_data['id'] = (int) $post->ID;
		}
		if ( isset( $schema['properties']['name'] ) ) {
			$post_data['name'] = $field->get_name();
		}
		if ( isset( $schema['properties']['label'] ) ) {
			$post_data['label'] = $field->get_label();
		}
		if ( isset( $schema['properties']['meta'] ) ) {
			$post_data['meta'] = $field->get_meta();
		}
		if ( isset( $schema['properties']['priority'] ) ) {
			$post_data['priority'] = (int) $field->get_priority();
		}
		if ( isset( $schema['properties']['default'] ) ) {
			$post_data['default'] = (bool) $field->is_default_field();
		}
		if ( isset( $schema['properties']['type'] ) ) {
			$post_data['type'] = $field->get_type();
		}
		if ( isset( $schema['properties']['description'] ) ) {
			$post_data['description'] = $field->get_description();
		}
		if ( isset( $schema['properties']['placeholder'] ) ) {
			$post_data['placeholder'] = $field->get_placeholder();
		}
		if ( isset( $schema['properties']['required'] ) ) {
			$post_data['required'] = (bool) $field->is_required();
		}
		if ( isset( $schema['properties']['read_only'] ) ) {
			$post_data['read_only'] = (bool) $field->is_read_only();
		}
		if ( isset( $schema['properties']['admin_only'] ) ) {
			$post_data['admin_only'] = (bool) $field->is_admin_only();
		}
		if ( isset( $schema['properties']['selectable_options'] ) ) {
			$post_data['selectable_options'] = (bool) $field->get_selectable_options();
		}
		if ( isset( $schema['properties']['file_size'] ) ) {
			$post_data['file_size'] = (bool) $field->get_file_size();
		}

		$response = rest_ensure_response( $post_data );
		$response->add_links( $this->prepare_links( $field, $request ) );

		return rest_ensure_response( $response );

	}

	/**
	 * Prepare links for the request.
	 *
	 * @param PNO_Profile_Field         $object  Object data.
	 * @param WP_REST_Request $request Request object.
	 * @return array                   Links for the given post.
	 */
	protected function prepare_links( $object, $request ) {
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( current_user_can( 'manage_options' ) ) {
			$admin_url = admin_url( 'post.php' );
			$admin_url = add_query_arg(
				[
					'post'   => $object->get_id(),
					'action' => 'edit',
				], $admin_url
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
			return new WP_Error( 'posterno_rest_cannot_create_exists', __( 'Cannot create existing field.' ), array( 'status' => 400 ) );
		}

		$field_name     = isset( $request['name'] ) ? sanitize_text_field( $request['name'] ) : false;
		$field_priority = isset( $_POST['priority'] ) && ! empty( $_POST['priority'] ) ? absint( $_POST['priority'] ) : false;

		$registered_field_types = pno_get_registered_field_types();
		$field_type             = isset( $request['type'] ) && isset( $registered_field_types[ $request['type'] ] ) ? sanitize_text_field( $request['type'] ) : false;

		if ( ! $field_name ) {
			return new WP_REST_Response( esc_html__( 'Please enter a name for the new field.' ), 422 );
		}

		if ( ! $field_type ) {
			return new WP_REST_Response( esc_html__( 'Invalid field type.' ), 422 );
		}

		$field = new PNO_Profile_Field();
		$field->__set( 'name', $field_name );
		$field->__set( 'type', $field_type );

		if ( $field_priority ) {
			$field->__set( 'priority', $field_priority );
		}

		$field->create();

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $field, $request );
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
			return new WP_REST_Response( esc_html__( 'Something went wrong while deleting the field, please contact support.' ), 422 );
		}

		$field = new PNO_Profile_Field( $field_id );

		if ( $field instanceof PNO_Profile_Field && $field->get_id() > 0 ) {

			$field_meta = $field->get_meta();

			if ( $field_meta && in_array( $field_meta, pno_get_registered_default_meta_keys() ) ) {
				return new WP_REST_Response( esc_html__( 'Default fields cannnot be deleted.' ), 422 );
			}

			$field->delete();

		}

		return rest_ensure_response( $field_id );

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
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'               => array(
					'description' => __( 'The name for the profile field.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'label'              => array(
					'description' => __( 'The optional label for the profile field used within forms.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'meta'               => array(
					'description' => __( 'The user meta key for the field used to store users information.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'priority'           => array(
					'description' => __( 'The priority number assigned to the field used to defined the order within forms.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'default'            => array(
					'description' => __( 'Flag to determine if the field is a default field.' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'type'               => array(
					'description' => __( 'Field type.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'description'        => array(
					'description' => __( 'Field description.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'placeholder'        => array(
					'description' => __( 'Field placeholder.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'required'           => array(
					'description' => __( 'Flag to determine if the field is required when displayed within forms.' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'read_only'          => array(
					'description' => __( 'Flag to determine if the field is read-only when displayed within forms.' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'admin_only'         => array(
					'description' => __( 'Flag to determine if the field is admin only and hidden from forms.' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'selectable_options' => array(
					'description' => __( 'Holds selectable options if the field needs them. Example: dropdown or multiselect fields.' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
					),
					'context'     => array( 'view', 'edit' ),
				),
				'file_size'          => array(
					'description' => __( 'Max file size assigned to the field if the type is file.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

		return $schema;

	}

	/*
	public function get_profile_fields( WP_REST_Request $request ) {

		$fields            = [];
		$registered_fields = pno_get_account_fields( false, true );
		$registered_types  = pno_get_registered_field_types();

		if ( is_array( $registered_fields ) && ! empty( $registered_fields ) ) {
			foreach ( $registered_fields as $field_key => $field ) {

				// Skip fields if defined.
				if ( isset( $field['show_in_ui'] ) && $field['show_in_ui'] === false ) {
					continue;
				}

				$field_in_db = $this->get_user_field( $field_key, $field );

				$fields[ $field_key ] = [
					'title'       => esc_html( $field['label'] ),
					'type'        => isset( $registered_types[ $field['type'] ] ) ? $registered_types[ $field['type'] ] : esc_html__( 'Unknown field type' ),
					'required'    => isset( $field['required'] ) && $field['required'] === true ? true : false,
					'priority'    => absint( $field['priority'] ),
					'default'     => pno_is_default_profile_field( $field_key ),
					'editable'    => $this->profile_field_is_editable( $field_in_db ),
					'field_db_id' => $field_in_db,
					'url'         => is_int( $field_in_db ) ? esc_url_raw(
						add_query_arg(
							[
								'post'   => $field_in_db,
								'action' => 'edit',
							], admin_url( 'post.php' )
						)
					) : false,
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


	public function update_profile_fields_order( WP_REST_Request $request ) {

		$fields = isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ? $_POST['fields'] : false;

		if ( ! $fields ) {
			return new WP_REST_Response( esc_html__( 'Something went wrong while updating the order of the fields, please contact support.' ), 422 );
		}

		foreach ( $fields as $key => $field ) {

			$field_id = isset( $field['field_db_id'] ) ? absint( $field['field_db_id'] ) : false;

			if ( $field_id ) {
				update_post_meta( $field_id, 'field_priority', $key );
			}
		}

		return rest_ensure_response( $fields );

	}


	public function create_profile_field( WP_REST_Request $request ) {

		$field_name     = isset( $_POST['field_name'] ) && ! empty( $_POST['field_name'] ) ? sanitize_text_field( $_POST['field_name'] ) : false;
		$field_priority = isset( $_POST['priority'] ) && ! empty( $_POST['priority'] ) ? absint( $_POST['priority'] ) : false;

		$registered_field_types = pno_get_registered_field_types();
		$field_type             = isset( $_POST['field_type'] ) && ! empty( $_POST['field_type'] ) && isset( $registered_field_types[ $_POST['field_type'] ] ) ? sanitize_text_field( $_POST['field_type'] ) : false;

		if ( ! $field_name ) {
			return new WP_REST_Response( esc_html__( 'Please enter a name for the new field.' ), 422 );
		}

		if ( ! $field_type ) {
			return new WP_REST_Response( esc_html__( 'Invalid field type.' ), 422 );
		}

		if ( ! $field_priority ) {
			return new WP_REST_Response( esc_html__( 'Invalid priority.' ), 422 );
		}

		$new_field = [
			'post_type'   => 'pno_users_fields',
			'post_title'  => $field_name,
			'post_status' => 'publish',
		];

		$field_id = wp_insert_post( $new_field );
		$return   = '';

		if ( is_wp_error( $field_id ) ) {
				return new WP_REST_Response( $field_id->get_error_message(), 422 );
		} else {

			// Set the type of the field.
			carbon_set_post_meta( $field_id, 'field_type', $field_type );

			// Set the order priority for this new field.
			update_post_meta( $field_id, 'field_priority', $field_priority + 1 );

			// Setup the user meta key for the field.
			$meta = get_post_field( 'post_name', $field_id );
			$meta = sanitize_title( $meta );
			$meta = str_replace( '-', '_', $meta );

			carbon_set_post_meta( $field_id, 'field_meta_key', $meta );

			$return = add_query_arg(
				[
					'post'   => $field_id,
					'action' => 'edit',
				], admin_url( 'post.php' )
			);

		}

		return rest_ensure_response( urlencode( $return ) );

	}


	public function delete_profile_field( WP_REST_Request $request ) {

		$field_id = isset( $_POST['field_id'] ) && ! empty( $_POST['field_id'] ) ? absint( $_POST['field_id'] ) : false;

		if ( ! $field_id ) {
			return new WP_REST_Response( esc_html__( 'Something went wrong while deleting the field, please contact support.' ), 422 );
		}

		$field_meta = carbon_get_post_meta( $field_id, 'field_meta_key' );

		if ( $field_meta && in_array( $field_meta, pno_get_registered_default_meta_keys() ) ) {
			return new WP_REST_Response( esc_html__( 'Default fields cannnot be deleted.' ), 422 );
		}

		wp_delete_post( $field_id, true );

		return rest_ensure_response();

	}*/

}
