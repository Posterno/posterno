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
		$registered_fields = pno_get_account_fields( false, true );
		$registered_types  = pno_get_registered_field_types();

		if ( is_array( $registered_fields ) && ! empty( $registered_fields ) ) {
			foreach ( $registered_fields as $field_key => $field ) {

				// Skip fields if defined.
				if ( isset( $field['show_in_ui'] ) && $field['show_in_ui'] === false ) {
					continue;
				}

				$field_in_db = $this->maybe_create_user_field( $field_key, $field );

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

	/**
	 * Determine if we're going to create a field into the database or not.
	 * Each field is a post registered within the pno_users_fields post type.
	 *
	 * @param string $field_key
	 * @param array $field
	 * @return void
	 */
	private function maybe_create_user_field( $field_key, $field ) {

		if ( ! $field_key ) {
			return;
		}

		if ( ! pno_is_default_profile_field( $field_key ) ) {
			return;
		}

		$args = [
			'post_type'              => 'pno_users_fields',
			'posts_per_page'         => 1,
			'nopaging'               => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				'relation'    => 'AND',
				'type_clause' => array(
					'key'   => 'field_meta_key',
					'value' => $field_key,
				),
			),
		];

		$field_query = new WP_Query( $args );

		// If we've found the field, we return the field's object.
		// If not, we create the field into the database.
		if ( $field_query->have_posts() ) {

			while ( $field_query->have_posts() ) :

				$field_query->the_post();

				return get_the_ID();

			endwhile;

		} else {

			$new_field = [
				'post_type'   => 'pno_users_fields',
				'post_title'  => $field['label'],
				'post_status' => 'publish',
			];

			$field_id = wp_insert_post( $new_field );

			if ( is_wp_error( $field_id ) ) {
				return new WP_REST_Response( $field_id->get_error_message(), 422 );
			} else {

				// Setup the field's meta key.
				carbon_set_post_meta( $field_id, 'field_meta_key', $field_key );

				// Setup the field's type.
				$registered_field_types = pno_get_registered_field_types();

				if ( isset( $field['type'] ) && isset( $registered_field_types[ $field['type'] ] ) ) {
					carbon_set_post_meta( $field_id, 'field_type', esc_attr( $field['type'] ) );
				}

				// Assign a description if one is given.
				if ( isset( $field['description'] ) && ! empty( $field['description'] ) ) {
					carbon_set_post_meta( $field_id, 'field_description', esc_html( $field['description'] ) );
				}

				// Assign a placeholder if one is given.
				if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) {
					carbon_set_post_meta( $field_id, 'field_placeholder', esc_html( $field['placeholder'] ) );
				}

				// Make field required if defined.
				if ( isset( $field['required'] ) && $field['required'] === true ) {
					carbon_set_post_meta( $field_id, 'field_is_required', true );
				}

				// Mark the field as a default one.
				if ( pno_is_default_profile_field( $field_key ) ) {
					update_post_meta( $field_id, 'is_default_field', true );
				}

				/**
				 * Allow developers to extend the profile field's creation
				 * into the database when the field is first registered.
				 *
				 * @param string $field_id the id of the post into the db.
				 * @param string $field_key the unique key for the field.
				 * @param array $field the default settings of the field.
				 */
				do_action( 'pno_after_profile_field_is_created', $field_id, $field_key, $field );

			}

			wp_reset_postdata();

			return $field_id;

		}

	}

	/**
	 * Determines the editability level of a given profile field.
	 *
	 * @return void
	 */
	private function profile_field_is_editable( $field_id ) {

		if ( ! $field_id ) {
			return;
		}

		$editable = true;

		if ( carbon_get_post_meta( $field_id, 'field_is_hidden' ) ) {
			$editable = 'admin_only';
		}

		return $editable;

	}

	/**
	 * Save the order of the fields when updated in the admin panel.
	 *
	 * @param WP_REST_Request $request
	 * @return void
	 */
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

	/**
	 * Create a new profile field through the api.
	 *
	 * @param WP_REST_Request $request
	 * @return void
	 */
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
			$meta = sanitize_title( $field_name );
			$meta = str_replace( '-', '_', $meta );

			carbon_set_post_meta( $field_id, 'field_meta_key', $meta );

			$return = add_query_arg(
				[
					'post'   => $field_id,
					'action' => 'edit',
				], admin_url( 'post.php' )
			);

		}

		return rest_ensure_response( $return );

	}

}
