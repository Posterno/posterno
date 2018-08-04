<?php
/**
 * Main class that handles methods for custom fields of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

abstract class PNO_Field_Object {

	/**
	 * Field ID from the database.
	 *
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Field meta key that will be used to store values into the database.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $meta = null;

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
	 * @param string $key The attribute to get
	 * @return boolean If the item is set or not
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return false === empty( $this->{$key} );
		} else {
			return null;
		}
	}

	/**
	 * Verify if the field exists into the database.
	 *
	 * @param int $field_id
	 * @return void
	 */
	protected function get_field( $field_id ) {

		if ( ! $field_id ) {
			return;
		}

		$field_args = [
			'post_type'              => $this->post_type,
			'p'                      => absint( $field_id ),
			'nopaging'               => true,
			'no_found_rows'          => true,
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
	 * Get the ID number of the field from the database.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the name of the field.
	 *
	 * @return void
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the meta of the field.
	 *
	 * @return void
	 */
	public function get_meta() {
		return $this->meta;
	}

	/**
	 * Get the form label for this field.
	 *
	 * @return void
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get the description for forms assigned to the field.
	 *
	 * @return void
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
	 * @return void
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
		return apply_filters( "pno_field_{$this->meta}_selectable_options", $this->selectable_options );
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
	 * Sanitize the value for priority submission.
	 *
	 * @param integer $priority
	 * @return void
	 */
	public function set_priority( $key, $value ) {
		$this->priority = absint( $value );
	}

	/**
	 * Once object variables has been set, an update is needed to persist them to the database.
	 *
	 * @return bool True if the save was successful, false if it failed or wasn't needed.
	 */
	public function save() {

		$saved = false;

		if ( empty( $this->id ) ) {
			$field_id = $this->create();
			if ( false === $field_id ) {
				$saved = false;
			} else {
				$this->id = $field_id;
			}
		}

		if ( ! empty( $this->pending ) ) {
			foreach ( $this->pending as $key => $value ) {
				$this->update_meta( $key, $value );
				if ( 'name' == $key && ! empty( $value ) ) {
					wp_update_post(
						array(
							'ID'         => $this->id,
							'post_title' => $value,
						)
					);
				}
			}
			$saved = true;
		}

		if ( true == $saved ) {
			$this->setup_field( WP_Post::get_instance( $this->id ) );
		}

		return $saved;

	}

	/**
	 * Update a meta setting value related to this field.
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function update_meta( $key = '', $value = '' ) {

		if ( empty( $key ) || '' == $key ) {
			return false;
		}

		switch ( $key ) {
			case 'required':
			case 'read_only':
			case 'admin_only':
				$key = 'field_is_' . $key;
				break;
			case 'meta':
				$key = 'field_meta_key';
				break;
			default:
				$key = 'field_' . $key;
				break;
		}

		return carbon_set_post_meta( $this->id, $key, $value );

	}

	/**
	 * Update an existing field in the database.
	 *
	 * @param array $args field details.
	 * @return void mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new field id.
	 */
	public function update( $args = [] ) {

		$meta = $this->build_meta( $args );

		if ( isset( $meta['name'] ) && ! empty( $meta['name'] ) ) {
			wp_update_post(
				array(
					'ID'         => $this->id,
					'post_title' => $meta['name'],
				)
			);
		}

		foreach ( $meta as $key => $value ) {
			$this->update_meta( $key, $value );
		}

		$this->setup_field( WP_Post::get_instance( $this->id ) );

		return $this->id;

	}

	/**
	 * Delete the field from the database.
	 *
	 * @return mixed The post object (if it was deleted or moved to the trash successfully) or false (failure).
	 */
	public function delete() {

		if ( $this->id > 0 ) {
			return wp_delete_post( $this->id, true );
		} else {
			return false;
		}

	}

}
