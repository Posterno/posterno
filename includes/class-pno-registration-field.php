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
class PNO_Registration_Field {

	/**
	 * Field ID.
	 *
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Field profile ID.
	 *
	 * @access protected
	 * @var int
	 */
	protected $profile_field_id = 0;

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
			'post_type'              => 'pno_signup_fields',
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

	}

}
