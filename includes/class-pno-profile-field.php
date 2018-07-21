<?php
/**
 * Abstraction layer for the profile fields.
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

class PNO_Profile_Field {

	/**
	 * Field ID.
	 *
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Field meta key.
	 *
	 * @access protected
	 * @var int
	 */
	protected $meta = null;

	/**
	 * Priority order.
	 *
	 * @access protected
	 * @var int
	 */
	protected $priority = 0;

	/**
	 * Wether the field is a default field or not.
	 *
	 * @var boolean
	 */
	protected $default = false;

	/**
	 * The field type.
	 *
	 * @var boolean
	 */
	protected $type = false;

	/**
	 * The nicename of the field type.
	 *
	 * @var string
	 */
	protected $type_nicename = null;

	/**
	 * Field Name.
	 *
	 * @access protected
	 * @var string
	 */
	protected $name = null;

	/**
	 * Field form label.
	 *
	 * @access protected
	 * @var string
	 */
	protected $label = null;

	/**
	 * Field description.
	 *
	 * @access protected
	 * @var string
	 */
	protected $description = null;

	/**
	 * Field placeholder.
	 *
	 * @access protected
	 * @var string
	 */
	protected $placeholder = null;

	/**
	 * Determine wether the field is required or not.
	 *
	 * @var boolean
	 */
	protected $required = false;

	/**
	 * Holds the value of the field if a user ID is given for this field.
	 *
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * Constructor.
	 *
	 * @param mixed|boolean $_id
	 */
	public function __construct( $_id_or_field = false, $user_id = false ) {

		if ( empty( $_id_or_field ) ) {
			return false;
		}

		$field = $this->get_field( $_id_or_field );

		if ( $field ) {
			$this->setup_field( $field );
		} else {
			return false;
		}

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property.
	 *
	 * @param string $key
	 * @return void
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t get property %s' ), $key ) );
		}
	}

	/**
	 * Verify if the field exists into the database.
	 *
	 * @param int $field_id
	 * @return void
	 */
	private function get_field( $field_id ) {

		if ( ! $field_id ) {
			return;
		}

		$field_args = [
			'post_type'              => 'pno_users_fields',
			'p'                      => absint( $field_id ),
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		];

		$field       = new WP_Query( $field_args );
		$found_field = $field->get_posts();

		wp_reset_postdata();

		if ( is_array( $found_field ) && ! empty( $found_field ) && isset( $found_field[0] ) ) {
			return $field_id;
		} else {
			return false;
		}

	}

	/**
	 * Setup the properties for the field by retrieving it's data.
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

		$this->id            = $field_id;
		$this->meta          = carbon_get_post_meta( $this->id, 'field_meta_key' );
		$this->default       = pno_is_default_profile_field( $this->meta ) || get_post_meta( $this->id, 'is_default_field', true ) ? true : false;
		$this->type          = carbon_get_post_meta( $this->id, 'field_type' );
		$types               = pno_get_registered_field_types();
		$this->type_nicename = isset( $types[ $this->type ] ) ? $types[ $this->type ] : false;
		$this->name          = get_the_title( $this->id );

		$label = carbon_get_post_meta( $this->id, 'field_label' );

		if ( $label && ! empty( $label ) ) {
			$this->label = $label;
		} else {
			$this->label = $this->name;
		}

		$this->description = carbon_get_post_meta( $this->id, 'field_description' );
		$this->placeholder = carbon_get_post_meta( $this->id, 'field_placeholder' );
		$this->required    = carbon_get_post_meta( $this->id, 'field_is_required' );

	}

	/**
	 * Get the ID of the field.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the meta key of the field. The key is used to store data for profiles.
	 *
	 * @return string
	 */
	public function get_meta() {
		return $this->meta;
	}

	/**
	 * Wether or not the field is a default field.
	 *
	 * @return boolean
	 */
	public function is_default_field() {
		return (bool) $this->default;
	}

	/**
	 * Get the field type. The type is used to load the appropriate field template within forms.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the localized field type human readable name.
	 *
	 * @return string
	 */
	public function get_type_nicename() {
		return $this->type_nicename;
	}

	/**
	 * Get the name of the field.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the label of the field. If no label is specified, then use the field title.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get the description of the field for the forms.
	 *
	 * @return mixed
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get a placeholder for the field within forms if specified.
	 *
	 * @return mixed
	 */
	public function get_placeholder() {
		return $this->placeholder;
	}

	/**
	 * Flag to detect if the field is required or not.
	 *
	 * @return boolean
	 */
	public function is_required() {
		return (bool) $this->required;
	}

}
