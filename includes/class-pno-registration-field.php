<?php
/**
 * Abstraction layer for the registration fields.
 *
 * By giving a post ID to the class, we retrieve an object containing
 * all the info we need about the profile field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles registration fields.
 */
class PNO_Registration_Field extends PNO_Field_Object {

	/**
	 * Field profile ID.
	 *
	 * @access protected
	 * @var int
	 */
	protected $profile_field_id = 0;

	/**
	 * Role assigned to this field.
	 *
	 * @var mixed
	 */
	protected $role = false;

	/**
	 * The post type for this field type.
	 *
	 * @var string
	 */
	public $post_type = 'pno_signup_fields';

	/**
	 * Instantiate the registration field.
	 *
	 * @param boolean $_id_or_field
	 */
	public function __construct( $_id_or_field = false ) {

		if ( empty( $_id_or_field ) ) {
			return false;
		}

		if ( $_id_or_field instanceof PNO_Registration_Field ) {
			return $_id_or_field;
		}

		$field = $this->get_field( $_id_or_field );

		if ( $field ) {
			$this->setup_field( $field );
		} else {
			return false;
		}

	}

	/**
	 * Setup the properties of this field after the field has been confirmed to exist.
	 *
	 * @param int $field_id
	 * @return void
	 */
	private function setup_field( $field_id ) {

		if ( null == $field_id ) {
			return false;
		}

		if ( ! is_int( $field_id ) ) {
			return false;
		}

		if ( is_wp_error( $field_id ) ) {
			return false;
		}

		$this->id   = $field_id;
		$this->name = get_the_title( $this->id );

		$label = carbon_get_post_meta( $this->id, 'field_label' );

		if ( $label && ! empty( $label ) ) {
			$this->label = $label;
		} else {
			$this->label = $this->name;
		}

		$this->description = carbon_get_post_meta( $this->id, 'field_description' );
		$this->placeholder = carbon_get_post_meta( $this->id, 'field_placeholder' );
		$this->required    = carbon_get_post_meta( $this->id, 'field_is_required' );
		$this->priority    = carbon_get_post_meta( $this->id, 'field_priority' );
		$this->meta        = carbon_get_post_meta( $this->id, 'field_is_default' );
		$this->default     = pno_is_default_profile_field( $this->meta ) || carbon_get_post_meta( $this->id, 'field_is_default' ) ? true : false;
		$types             = pno_get_registered_field_types();

		if ( ! empty( $this->default ) ) {

			$type = 'text';

			switch ( $this->meta ) {
				case 'password':
					$type = 'password';
					break;
				case 'email':
					$type = 'email';
					break;
			}

			$this->type          = $type;
			$this->type_nicename = isset( $types[ $this->type ] ) ? $types[ $this->type ] : false;

			// Force requirement for the email field.
			if ( $this->default == 'email' ) {
				$this->required = true;
			}
		}

		/**
		 * Allows developers to extend the setup process of
		 * registration fields.
		 *
		 * @param object $this the current field object.
		 */
		do_action( 'pno_setup_registration_field', $this );

	}

	/**
	 * Retrieve the role assigned to this field.
	 *
	 * By default in the free version,
	 * all custom registration fields are visible to all users.
	 *
	 * @return mixed
	 */
	public function get_role() {
		return $this->role;
	}

	/**
	 * Create a new registration field and store it into the database.
	 *
	 * @return mixed
	 */
	public function create() {

		$args = array(
			'name'             => isset( $this->name ) ? $this->name : '',
			'meta'             => isset( $this->meta ) ? $this->meta : '',
			'priority'         => isset( $this->priority ) ? $this->priority : false,
			'default'          => isset( $this->default ) ? $this->default : false,
			'type'             => isset( $this->type ) && ! empty( $this->type ) ? $this->type : 'text',
			'label'            => isset( $this->label ) ? $this->label : '',
			'description'      => isset( $this->description ) ? $this->description : '',
			'placeholder'      => isset( $this->placeholder ) ? $this->placeholder : '',
			'required'         => isset( $this->required ) ? $this->required : false,
			'profile_field_id' => isset( $this->profile_field_id ) ? $this->profile_field_id : false,
		);

		if ( empty( $args['name'] ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t find property %s' ), 'name' ) );
		}

		if ( empty( $args['profile_field_id'] ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t find property %s' ), 'profile_field_od' ) );
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
			$this->setup_field( $this->id );
		}

		return $this->id;

	}

	/**
	 * Build a registration field meta array.
	 *
	 * @param array $args registration field meta.
	 * @return mixed false if something was wrong, array containing sanitized settings.
	 */
	private function build_meta( $args = [] ) {

		if ( ! is_array( $args ) || array() === $args ) {
			return false;
		}

		$meta = [
			'name'             => isset( $args['name'] ) ? $args['name'] : '',
			'meta'             => isset( $args['meta'] ) ? $args['meta'] : '',
			'priority'         => isset( $args['priority'] ) ? $args['priority'] : 0,
			'default'          => isset( $args['default'] ) ? $args['default'] : false,
			'type'             => isset( $args['type'] ) ? $args['type'] : 'text',
			'label'            => isset( $args['label'] ) ? $args['label'] : '',
			'description'      => isset( $args['description'] ) ? $args['description'] : '',
			'placeholder'      => isset( $args['placeholder'] ) ? $args['placeholder'] : '',
			'required'         => isset( $args['required'] ) ? $args['required'] : false,
			'profile_field_id' => isset( $args['profile_field_id'] ) ? $args['profile_field_id'] : false,
		];

		return $meta;

	}

}
