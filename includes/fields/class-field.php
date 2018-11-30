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
	protected $post_id = 0;

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
	 * The prefix used by Carbon Fields to store field's settings.
	 *
	 * @var string
	 */
	protected $field_setting_prefix = '';

	/**
	 * The post type where the field is stored.
	 *
	 * @var boolean|string
	 */
	protected $post_type = false;

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
	 * The human readable name of the set field type.
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
	 * Determine wether the field is readonly or not.
	 *
	 * @var boolean
	 */
	protected $readonly = false;

	/**
	 * Determine wether the field is admin only or not.
	 *
	 * @var boolean
	 */
	protected $admin_only = false;

	/**
	 * Selectable options for dropdown fields.
	 *
	 * @var mixed
	 */
	protected $options = false;

	/**
	 * Allowed mime types for upload of file fields.
	 *
	 * @var boolean|array
	 */
	protected $allowed_mime_types = false;

	/**
	 * Determine if the field can store multiple values eg: arrays.
	 *
	 * @var boolean
	 */
	protected $multiple = false;

	/**
	 * Holds the max size for files uploadable through this field.
	 *
	 * @var string
	 */
	protected $maxsize = null;

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
	 * Holds the settings stored into the database for the given's field.
	 *
	 * @var boolean|string
	 */
	protected $settings = false;

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
			throw new \InvalidArgumentException( sprintf( __( 'Can\'t get property %s' ), $key ) );
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
	 * Retrieve the associated post id with the field.
	 *
	 * @return string
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * Get the ID used in the html templates.
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
	 * Flag to detect if the field is readonly or not.
	 *
	 * @return boolean
	 */
	public function is_readonly() {
		return (bool) $this->readonly;
	}

	/**
	 * Flag to detect whether the field is admin only.
	 *
	 * @return boolean
	 */
	public function is_admin_only() {
		return (bool) $this->admin_only;
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
		return apply_filters( "pno_field_{$this->object_meta_key}_selectable_options", $this->options );
	}

	/**
	 * Retrieve mime types defined for the field.
	 *
	 * @return mixed
	 */
	public function get_allowed_mime_types() {
		return $this->allowed_mime_types;
	}

	/**
	 * Verify if the field can store multiple values eg: arrays.
	 *
	 * @return boolean
	 */
	public function is_multiple() {
		return (bool) $this->multiple;
	}

	/**
	 * Retrieve the specified max size allowed for files within this field.
	 *
	 * @return null|string
	 */
	public function get_maxsize() {
		return $this->maxsize;
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
	 * Get an array of settings for the field, retrieved from the database.
	 *
	 * @return array
	 */
	public function get_settings() {
		return maybe_unserialize( $this->settings );
	}

	/**
	 * Retrieve the specified field setting prefix for this type of field.
	 *
	 * @return string
	 */
	public function get_field_setting_prefix() {
		return $this->field_setting_prefix;
	}

	/**
	 * Get the post type set for the field.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Get things started.
	 *
	 * @param boolean|array|int|string $field the field to initialize either an id or an array.
	 * @param boolean|string           $object_id the id of the object for which we're going to retrieve the value associated to the field.
	 */
	public function __construct( $field = false, $object_id = false ) {

		if ( is_object( $field ) ) {
			$this->populate( $field );
		} else {
			$this->populate_from_post_id( $field );
		}

		if ( $object_id ) {
			$this->load_value( $object_id );
		}

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

	/**
	 * Retrieve attributes that belong to the field.
	 *
	 * @param array $merge additional attributes that could belong to the field.
	 * @return mixed
	 */
	public function get_attributes( array $merge = array() ) {

		$result     = '';
		$attributes = [];

		if ( ! empty( $merge ) ) {
			$attributes = array_merge_recursive( $attributes, $merge );
		}

		if ( ! empty( $this->get_placeholder() ) ) {
			$attributes['placeholder'] = $this->get_placeholder();
		}
		if ( $this->is_required() ) {
			$attributes['required'] = false;
		}
		if ( $this->is_readonly() ) {
			$attributes['readonly'] = false;
		}

		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $name => $value ) {
				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}
				if ( $value ) {
					$result .= " {$name}=\"{$value}\"";
				} else {
					$result .= " {$name}";
				}
			}
		}

		return $result;
	}

	/**
	 * Remove a prefix from the field id.
	 *
	 * @param string $prefix string to remove.
	 * @param string $id field id.
	 * @return string
	 */
	public function remove_prefix_from_setting_id( $prefix, $id ) {
		return str_replace( $prefix, '', $id );
	}

	/**
	 * Populate field's object from post id.
	 *
	 * @param string $post_id the id of the post.
	 * @return void
	 */
	public function populate_from_post_id( $post_id ) {}

	/**
	 * Create a new field and save it into the database.
	 *
	 * @param array $args list of arguments to create a new field.
	 * @return void
	 */
	public function create( $args = [] ) {}

	/**
	 * Delete a field from the database and delete it's associated settings too.
	 *
	 * @return void
	 */
	public function delete() {}

	/**
	 * Load the value associated with the field.
	 *
	 * @param string $object_id the id of the object we're going to look up.
	 * @return void
	 */
	public function load_value( $object_id ) {}

	/**
	 * Update the priority of the field into the database.
	 *
	 * @param string $priority the new priority to set for the field.
	 * @return void
	 */
	public function update_priority( $priority ) {
		carbon_set_post_meta( $this->get_post_id(), $this->get_field_setting_prefix() . 'priority', absint( $priority ) );
	}

}
