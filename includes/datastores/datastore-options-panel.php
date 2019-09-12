<?php
/**
 * Custom datastore for storing options panels values.
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

/**
 * Stores serialized values in the database
 */
class OptionsPanel extends Datastore {

	/**
	 * Initialization tasks for concrete datastores.
	 **/
	public function init() {}

	/**
	 * Get field's name.
	 *
	 * @param Field $field the field to analyze.
	 * @return string
	 */
	protected function get_key_for_field( Field $field ) {
		$key = $field->get_base_name();
		return $key;
	}

	/**
	 * Load the field value(s)
	 *
	 * @param Field $field The field to load value(s) in.
	 * @return array
	 */
	public function load( Field $field ) {
		$key   = $this->get_key_for_field( $field );
		$value = pno_get_option( $key, null );
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
		$value = $field->get_formatted_value();
		if ( is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {
			$value = $field->get_value_tree();
		}
		pno_update_option( $key, $value );
	}

	/**
	 * Delete the field value(s)
	 *
	 * @param Field $field The field to delete.
	 */
	public function delete( Field $field ) {
		if ( ! empty( $field->get_hierarchy() ) ) {
			return;
		}
		$key = $this->get_key_for_field( $field );
		pno_delete_option( $key );
	}
}
