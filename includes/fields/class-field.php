<?php
/**
 * Global fields object.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

use PNO\Base_Object;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines methods and properties for all fields of Posterno.
 */
class Field extends Base_Object {

	/**
	 * Field ID from the database this is the id of the post type.
	 *
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Meta id from the database, this is the id stored into the metadata table of the field type.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $meta_id = 0;

	/**
	 * Field meta key that will be used to store values into the database.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $meta_key = null;

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
	 * Selectable options for dropdown fields.
	 *
	 * @var mixed
	 */
	protected $selectable_options = false;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer db calls to be run.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	private $pending;

	/**
	 * Post type from where to grab the field.
	 *
	 * @var mixed
	 */
	public $post_type = null;

	/**
	 * Value associated to the field.
	 *
	 * @var mixed
	 */
	protected $value = false;

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
	protected $classes = false;

	/**
	 * Max file size for files uploaded through this field.
	 *
	 * @var mixed
	 */
	protected $file_size = false;

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
	 * Get the ID number of the field from the database.
	 *
	 * @return string
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
	public function get_meta_key() {
		return $this->meta_key;
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
	 * @return string
	 */
	public function get_priority() {
		return absint( $this->priority );
	}

	/**
	 * Retrieve selectable options for this field if needed.
	 *
	 * @return mixed
	 */
	public function get_selectable_options() {
		return apply_filters( "pno_field_{$this->get_meta_key()}_selectable_options", $this->selectable_options );
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
	public function get_classes() {
		return $this->classes;
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
	 * Verify if the field exists into the database.
	 *
	 * @param int $field_id the id of the field to verify.
	 * @return mixed
	 */
	protected function get_field( $field_id ) {

		if ( ! $field_id ) {
			return;
		}

		$field_args = [
			'post_type'     => $this->post_type,
			'p'             => absint( $field_id ),
			'nopaging'      => true,
			'no_found_rows' => true,
			'fields'        => 'ids',
		];

		$field       = new \WP_Query( $field_args );
		$found_field = $field->get_posts();

		wp_reset_postdata();

		if ( is_array( $found_field ) && ! empty( $found_field ) && isset( $found_field[0] ) ) {
			return $field_id;
		} else {
			return false;
		}

	}

}
