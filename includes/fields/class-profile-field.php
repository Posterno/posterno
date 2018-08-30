<?php
/**
 * Profile field object.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Fields;

use PNO\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The profile field object class that defines
 * all properties and any additional methods of the object.
 */
class Profile_Field extends Field {

	/**
	 * The post type for this field type.
	 *
	 * @var string
	 */
	public $post_type = 'pno_users_fields';

	/**
	 * Loads details about the actual field from the post type.
	 *
	 * @param object $user_field the originally retrieved object from the database.
	 */
	public function __construct( $user_field = null ) {

		parent::__construct( $user_field );

		if ( is_object( $user_field ) ) {

			$profile_field_id = $this->get_field( absint( $user_field->object_id ) );
			$this->meta_id    = $user_field->id;

			if ( $profile_field_id ) {
				$this->setup_field( $profile_field_id );
			}
		}

	}

	/**
	 * Setup the properties for the field by retrieving it's data.
	 *
	 * @param string  $field_id the id of the field.
	 * @param boolean $user_id the id of the user if we need to retrieve a value associated to this field.
	 * @return mixed
	 */
	protected function setup_field( $field_id, $user_id = false ) {

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
		$types               = pno_get_registered_field_types();
		$this->type_nicename = isset( $types[ $this->type ] ) ? $types[ $this->type ] : false;
		$this->name          = get_the_title( $this->id );
		$this->default       = pno_is_default_profile_field( $this->meta_key );

		if ( $this->get_label() && ! empty( $this->get_label() ) ) {
			$this->label = $this->get_label();
		} else {
			$this->label = $this->get_name();
		}

	}

}
