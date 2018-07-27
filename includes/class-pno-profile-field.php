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
	 * Selectable options for dropdown fields.
	 *
	 * @var mixed
	 */
	protected $selectable_options = false;

	/**
	 * Max file size for files uploaded through this field.
	 *
	 * @var mixed
	 */
	protected $file_size = false;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer db calls to be run.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	private $pending;

	/**
	 * Constructor.
	 *
	 * @param mixed|boolean $_id
	 */
	public function __construct( $_id_or_field = false ) {

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

		$this->description    = carbon_get_post_meta( $this->id, 'field_description' );
		$this->placeholder    = carbon_get_post_meta( $this->id, 'field_placeholder' );
		$this->required       = carbon_get_post_meta( $this->id, 'field_is_required' );
		$this->read_only      = carbon_get_post_meta( $this->id, 'field_is_read_only' );
		$this->admin_only     = carbon_get_post_meta( $this->id, 'field_is_hidden' );
		$this->custom_classes = carbon_get_post_meta( $this->id, 'field_custom_classes' );
		$this->priority       = carbon_get_post_meta( $this->id, 'field_priority' );

		if ( in_array( $this->type, pno_get_multi_options_field_types() ) ) {
			$this->selectable_options = pno_parse_selectable_options( carbon_get_post_meta( $this->id, 'field_selectable_options' ) );
		}

		if ( $this->type == 'file' ) {
			$this->file_size = carbon_get_post_meta( $this->id, 'field_file_max_size' );
		}

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
	 * Get the priority set for the field.
	 *
	 * @return void
	 */
	public function get_priority() {
		return absint( $this->priority );
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
	 * Retrieve selectable options for this field if needed.
	 *
	 * @return mixed
	 */
	public function get_selectable_options() {
		return $this->selectable_options;
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

		$field_args = [
			'post_type'   => 'pno_users_fields',
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
	 * Update an existing profile field in the database.
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
	 * Delete the profile field from the database.
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
