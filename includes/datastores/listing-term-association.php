<?php
/**
 * Store association data into a single meta data row into the database so we can query it later.
 *
 * @package     posterno
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace PNO\Datastores;

use Carbon_Fields\Field\Field;
use Carbon_Fields\Datastore\Datastore;
use Carbon_Fields\Datastore\Meta_Datastore;
use Carbon_Fields\Datastore\Term_Meta_Datastore;

/**
 * Store association data into a single meta data row into the database so we can query it later.
 */
class ListingTermAssociation extends Term_Meta_Datastore {

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
	 * Load the field's value from the database.
	 *
	 * @param Field $field the field to query.
	 * @return mixed
	 */
	public function load( Field $field ) {

		if ( ! empty( $field->get_hierarchy() ) ) {
			return;
		}

		return get_term_meta( $this->get_object_id(), $this->get_key_for_field( $field ), true );

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

		if ( ! empty( $value ) ) {
			update_term_meta( $this->get_object_id(), $key, $value );
		}

	}

}
