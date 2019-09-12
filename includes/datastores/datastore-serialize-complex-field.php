<?php
/**
 * Custom datastore for listings metadata.
 *
 * @package     posterno
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Datastores;

use Carbon_Fields\Field\Field;
use Carbon_Fields\Datastore\Datastore;
use Carbon_Fields\Datastore\Meta_Datastore;
use Carbon_Fields\Datastore\Post_Meta_Datastore;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles storage of serialized fields for listings within a single post meta instead of multiple post metas.
 */
class SerializeComplexField extends Post_Meta_Datastore {

	/**
	 * Get the defined key for the field.
	 *
	 * @param Field $field field object.
	 * @return string
	 */
	protected function get_key_for_field( Field $field ) {
		$key = '_' . $field->get_base_name();
		return $key;
	}

	/**
	 * Retrieve value stored into the database.
	 *
	 * @param Field $field the field to load.
	 * @return mixed
	 */
	public function load( Field $field ) {
		$key   = $this->get_key_for_field( $field );
		$value = get_post_meta( $this->get_object_id(), $key, true );

		if ( empty( $value ) ) {
			$value = [];
		}

		return $value;
	}

	/**
	 * Save the field value(s)
	 *
	 * @param Field $field The field to save.
	 */
	public function save( Field $field ) {

		if ( ! empty( $field->get_hierarchy() ) ) {
			return;
		}

		$key   = $this->get_key_for_field( $field );
		$value = $field->get_full_value();

		if ( is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {
			$value = $field->get_value_tree();
		}

		if ( ! empty( $value ) && is_array( $value ) ) {
			if ( ! update_post_meta( $this->get_object_id(), $key, $value ) ) {
				add_post_meta( $this->get_object_id(), $key, $value, true );
			}
		} else {
			$this->delete( $field );
			return;
		}
	}

	/**
	 * Delete the field for the specified object id.
	 *
	 * @param Field $field field to deleted.
	 * @return void
	 */
	public function delete( Field $field ) {

		delete_post_meta( $this->get_object_id(), $this->get_key_for_field( $field ) );

	}

}
