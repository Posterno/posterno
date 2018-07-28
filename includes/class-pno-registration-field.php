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

			switch ( $this->default ) {
				case 'password':
					$type = 'password';
					break;
				case 'email':
					$type = 'email';
					break;
			}

			$this->type          = $type;
			$this->type_nicename = isset( $types[ $this->type ] ) ? $types[ $this->type ] : false;

		}

	}

}
