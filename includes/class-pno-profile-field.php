<?php
/**
 * Abstraction layer for the profile fields.
 *
 * By giving a post ID to the class, we retrieve an object containing
 * all the info we need about the profile field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * PNO Profile field object class.
 */
class PNO_Profile_Field extends PNO_Field_Object {

	/**
	 * Determine wether the field is read only or not.
	 *
	 * @var boolean
	 */
	protected $read_only = false;

	/**
	 * Determine wether the field is admin only or not.
	 *
	 * @var boolean
	 */
	protected $admin_only = false;

	/**
	 * Custom css classes.
	 *
	 * @var mixed
	 */
	protected $custom_classes = false;

	/**
	 * Max file size for files uploaded through this field.
	 *
	 * @var mixed
	 */
	protected $file_size = false;

	/**
	 * The post type for this field type.
	 *
	 * @var string
	 */
	public $post_type = 'pno_users_fields';

	/**
	 * Constructor.
	 *
	 * @param mixed|boolean $_id_or_field WP_Post of the field.
	 * @param string        $user_id optional user id to load value of the field from the db.
	 */
	public function __construct( $_id_or_field = false, $user_id = false ) {

		if ( empty( $_id_or_field ) ) {
			return false;
		}

		if ( $_id_or_field instanceof WP_post ) {
			$this->setup_field( $_id_or_field, $user_id );
		} else {
			$this->setup_field( get_post( $_id_or_field ), $user_id );
		}

	}

	/**
	 * Setup the properties for the field by retrieving it's data.
	 *
	 * @param WP_Post $field WP_Post object.
	 * @param string  $user_id optional user id to load value of the field from the db.
	 * @return mixed
	 */
	protected function setup_field( $field, $user_id = false ) {

		if ( null == $field ) {
			return false;
		}

		if ( is_wp_error( $field ) ) {
			return false;
		}

		$this->id            = $field->ID;
		$this->meta          = carbon_get_post_meta( $this->id, 'profile_field_meta_key' );
		$this->default       = pno_is_default_field( $this->meta ) || get_post_meta( $this->id, 'is_default_field', true ) ? true : false;
		$this->type          = carbon_get_post_meta( $this->id, 'profile_field_type' );
		$types               = pno_get_registered_field_types();
		$this->type_nicename = isset( $types[ $this->type ] ) ? $types[ $this->type ] : false;
		$this->name          = get_the_title( $this->id );

		$label = carbon_get_post_meta( $this->id, 'profile_field_label' );

		if ( $label && ! empty( $label ) ) {
			$this->label = $label;
		} else {
			$this->label = $this->name;
		}

		$this->description    = carbon_get_post_meta( $this->id, 'profile_field_description' );
		$this->placeholder    = carbon_get_post_meta( $this->id, 'profile_field_placeholder' );
		$this->required       = carbon_get_post_meta( $this->id, 'profile_field_is_required' );
		$this->read_only      = carbon_get_post_meta( $this->id, 'profile_field_is_read_only' );
		$this->admin_only     = carbon_get_post_meta( $this->id, 'profile_field_is_hidden' );
		$this->custom_classes = carbon_get_post_meta( $this->id, 'profile_field_custom_classes' );
		$this->priority       = carbon_get_post_meta( $this->id, 'profile_field_priority' );

		if ( in_array( $this->type, pno_get_multi_options_field_types() ) ) {
			$this->selectable_options = pno_parse_selectable_options( carbon_get_post_meta( $this->id, 'profile_field_selectable_options' ) );
		}

		if ( $this->type == 'file' ) {
			$this->file_size = carbon_get_post_meta( $this->id, 'profile_field_file_max_size' );
		}

		if ( $user_id ) {

			$meta_lookup = $this->meta;

			if ( $meta_lookup == 'avatar' ) {
				$meta_lookup = 'current_user_avatar';
			}

			$this->value = carbon_get_user_meta( $user_id, $meta_lookup );

		}

	}

	/**
	 * Flag to detect if the field is read only or not.
	 *
	 * @return boolean
	 */
	public function is_read_only() {
		return (bool) $this->read_only;
	}

	/**
	 * Flag to detect if the field is admin only or not.
	 *
	 * @return boolean
	 */
	public function is_admin_only() {
		return (bool) $this->admin_only;
	}

	/**
	 * Retrieve custom css classes applied to the field if any.
	 *
	 * @return string
	 */
	public function get_custom_classes() {
		return $this->custom_classes;
	}

	/**
	 * Retrieve the defined max file size for files uploaded through this field.
	 *
	 * @return mixed
	 */
	public function get_file_size() {
		return $this->file_size;
	}

	/**
	 * Create a new profile field and store it into the database.
	 *
	 * @return mixed
	 */
	public function create() {

		$args = array(
			'name'        => isset( $this->name ) ? $this->name : '',
			'meta'        => isset( $this->meta ) ? $this->meta : '',
			'priority'    => isset( $this->priority ) ? $this->priority : false,
			'default'     => isset( $this->default ) ? $this->default : false,
			'type'        => isset( $this->type ) && ! empty( $this->type ) ? $this->type : 'text',
			'label'       => isset( $this->label ) ? $this->label : '',
			'description' => isset( $this->description ) ? $this->description : '',
			'placeholder' => isset( $this->placeholder ) ? $this->placeholder : '',
			'required'    => isset( $this->required ) ? $this->required : false,
			'read_only'   => isset( $this->read_only ) ? $this->read_only : false,
			'admin_only'  => isset( $this->admin_only ) ? $this->admin_only : false,
		);

		if ( empty( $args['name'] ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t find property %s' ), 'name' ) );
		}

		if ( empty( $args['meta'] ) ) {
			$meta         = sanitize_title( $args['name'] );
			$meta         = str_replace( '-', '_', $meta );
			$args['meta'] = $meta;
		}

		if ( $this->field_meta_key_exists( $args['meta'] ) ) {
			return new WP_Error( 'field-meta-exists', esc_html__( 'Field meta key already exists. Please choose a different name.' ) );
		}

		$field_args = [
			'post_type'   => $this->post_type,
			'post_title'  => $args['name'],
			'post_status' => 'publish',
		];

		$field_id = wp_insert_post( $field_args );

		if ( ! is_wp_error( $field_id ) ) {
			$this->id = $field_id;
			foreach ( $args as $key => $value ) {
				if ( ! empty( $value ) ) {
					$this->update_meta( $key, $value );
				}
			}
			$this->setup_field( get_post( $this->id ) );
		}

		return $this->id;

	}

	/**
	 * Build a profile field meta array.
	 *
	 * @param array $args profile field meta.
	 * @return mixed false if something was wrong, array containing sanitized settings.
	 */
	private function build_meta( $args = [] ) {

		if ( ! is_array( $args ) || array() === $args ) {
			return false;
		}

		$meta = [
			'name'        => isset( $args['name'] ) ? $args['name'] : '',
			'meta'        => isset( $args['meta'] ) ? $args['meta'] : '',
			'priority'    => isset( $args['priority'] ) ? $args['priority'] : 0,
			'default'     => isset( $args['default'] ) ? $args['default'] : false,
			'type'        => isset( $args['type'] ) ? $args['type'] : 'text',
			'label'       => isset( $args['label'] ) ? $args['label'] : '',
			'description' => isset( $args['description'] ) ? $args['description'] : '',
			'placeholder' => isset( $args['placeholder'] ) ? $args['placeholder'] : '',
			'required'    => isset( $args['required'] ) ? $args['required'] : false,
			'read_only'   => isset( $args['read_only'] ) ? $args['read_only'] : false,
			'admin_only'  => isset( $args['admin_only'] ) ? $args['admin_only'] : false,
		];

		return $meta;

	}

	/**
	 * Delete the field from the database, also delete the field from the registration form if it exists.
	 *
	 * @return mixed The post object (if it was deleted or moved to the trash successfully) or false (failure).
	 */
	public function delete() {

		if ( $this->id > 0 ) {

			$registration_args = [
				'post_type'              => 'pno_signup_fields',
				'nopaging'               => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'fields'                 => 'ids',
				'post_status'            => 'publish',
				'meta_query'             => array(
					array(
						'key'     => 'registration_field_profile_field_id',
						'value'   => $this->id,
						'compare' => '==',
					),
				),
			];

			$registration_query = new WP_Query( $registration_args );

			if ( is_array( $registration_query->get_posts() ) && ! empty( $registration_query->get_posts() ) ) {
				foreach ( $registration_query->get_posts() as $registration_field_id ) {
					$registration_field = new PNO_Registration_Field( $registration_field_id );
					if ( $registration_field instanceof PNO_Registration_Field && $registration_field->get_id() > 0 ) {
						$registration_field->delete();
					}
				}
			}

			return wp_delete_post( $this->id, true );
		} else {
			return false;
		}

	}

}
