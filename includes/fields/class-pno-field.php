<?php
/**
 * Defines a representation of a Posterno powered field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Common properties and methods for all posterno's powered fields.
 */
class Field {

	/**
	 * Field ID from the database.
	 *
	 * @access protected
	 * @var int
	 */
	protected $object_id = 0;

	/**
	 * The type of data the field will'be storing into the database.
	 *
	 * Available types: post_meta, user_meta.
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $object_type = null;

	/**
	 * Field meta key that will be used to store values into the database.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $object_meta_key = null;

	/**
	 * ID attribute used within forms and templates.
	 *
	 * @access protected
	 * @var string
	 */
	protected $id = null;

	/**
	 * Field priority number used to determine the order of the field.
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
	protected $can_delete = true;

	/**
	 * The field type.
	 *
	 * @var boolean
	 */
	protected $type = false;

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
	 * Selectable options for dropdown fields.
	 *
	 * @var mixed
	 */
	protected $options = false;

	/**
	 * Value associated to the field.
	 *
	 * @var mixed
	 */
	protected $value = false;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer db calls to be run.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	private $pending;

	/**
	 * Magic __get function to dispatch a call to retrieve a private property.
	 *
	 * @param string $key the property to get.
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t get property %s' ), $key ) );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @see set()
	 *
	 * @param string $key   Property name.
	 * @param mixed  $value Property value.
	 */
	public function __set( $key, $value ) {

		$key = sanitize_key( $key );

		// Only real properties can be saved.
		$keys = array_keys( get_class_vars( get_called_class() ) );

		if ( ! in_array( $key, $keys ) ) {
			return false;
		}

		$this->pending[ $key ] = $value;

		// Dispatch to setter method if value needs to be sanitized.
		if ( method_exists( $this, 'set_' . $key ) ) {
			return call_user_func( array( $this, 'set_' . $key ), $key, $value );
		} else {
			$this->{$key} = $value;
		}

	}

	/**
	 * Magic __isset method to allow empty checks on protected elements
	 *
	 * @param string $key The attribute to get.
	 * @return boolean If the item is set or not.
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return false === empty( $this->{$key} );
		} else {
			return null;
		}
	}

	/**
	 * Get the ID number of the field from the database.
	 */
	public function get_id() {
		return $this->id;
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
	 * Get the meta of the field.
	 *
	 * @return string
	 */
	public function get_meta() {
		return $this->meta;
	}

	/**
	 * Get the form label for this field.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get the description for forms assigned to the field.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Determine if the field can be deleted or not.
	 *
	 * @return boolean
	 */
	public function can_delete() {
		return (bool) $this->can_delete;
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

	/**
	 * Get the priority set for the field.
	 *
	 * @return int
	 */
	public function get_priority() {
		return absint( $this->priority );
	}

	/**
	 * Get the value associated with the field.
	 *
	 * @return mixed
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Retrieve selectable options for this field if needed.
	 *
	 * @return mixed
	 */
	public function get_options() {
		return apply_filters( "pno_field_{$this->meta}_selectable_options", $this->options );
	}

	/**
	 * Retrieve the id of the field from the database.
	 *
	 * @return string
	 */
	public function get_object_id() {
		return absint( $this->object_id );
	}

	/**
	 * Retrieve the object specified for this field.
	 *
	 * @return string
	 */
	public function get_object_type() {
		return $this->object_type;
	}

	/**
	 * Retrieve the object meta key that will be used to store values
	 * associated with this field.
	 *
	 * @return string
	 */
	public function get_object_meta_key() {
		return $this->object_meta_key;
	}

	/**
	 * Get things started.
	 *
	 * @param boolean|array|int|string $_id_or_field the field to initialize either an id or an array.
	 */
	public function __construct( $_id_or_field = false ) {

		if ( ! $_id_or_field ) {
			return;
		}

		$this->populate( $_id_or_field );

	}

	/**
	 * Populate the object based on the data available within the field's data.
	 *
	 * @param mixed $args all the details available for a field.
	 * @return mixed
	 */
	private function populate( $args = [] ) {

		if ( empty( $args ) ) {
			return;
		}

		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}

	}

	public function get_attributes() {

	}

}
