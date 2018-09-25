<?php
/**
 * Custom datastore for storing custom fields details. All details that belog to
 * a single posterno custom field will be stored into a single metadata row into the database
 * to preserve space and increase performances.
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
use Carbon_Fields\Datastore\Post_Meta_Datastore;
use Carbon_Fields\Toolset\Key_Toolset;

/**
 * Store all details that belong to a posterno custom field into a single metadata row into the database.
 */
class CustomFieldsDetails extends Post_Meta_Datastore {

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
	 * Load the value of a given setting for the field.
	 *
	 * @param Field $field the field we're working with.
	 * @return mixed
	 */
	public function load( Field $field ) {

		if ( ! empty( $field->get_hierarchy() ) ) {
			return;
		}

		$key            = $this->get_key_for_field( $field );
		$field_settings = get_post_meta( $this->get_object_id(), '_field_settings', true );
		$value          = '';

		if ( is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {

			$value = isset( $field_settings[ $key ] ) ? $field_settings[ $key ] : [];

		} elseif ( is_a( $field, '\\Carbon_Fields\\Field\\Checkbox_Field' ) && isset( $field_settings[ $key ] ) && ! empty( $field_settings[ $key ] ) ) {

			$value = $field_settings[ $key ] === '1' ? 'yes' : false;

		} else {
			if ( isset( $field_settings[ $key ] ) && ! empty( $field_settings[ $key ] ) ) {
				$value = esc_html( $field_settings[ $key ] );
			}
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

		$key            = $this->get_key_for_field( $field );
		$value          = $field->get_formatted_value();
		$field_settings = get_post_meta( $this->get_object_id(), '_field_settings', true );

		if ( empty( $field_settings ) || ! is_array( $field_settings ) ) {
			$field_settings = [];
		}

		if ( empty( $value ) ) {
			if ( isset( $field_settings[ $key ] ) ) {
				unset( $field_settings[ $key ] );
			}
		} else {

			if ( is_array( $value ) && is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {

				$formatted_options = [];

				foreach ( $value as $optkey => $array_of_options ) {
					$array_of_options = array_map( 'sanitize_text_field', $array_of_options );
					$formatted_options[ $optkey ] = $array_of_options;
				}

				$value = $formatted_options;

			} else {
				$value = sanitize_text_field( $value );
			}

			$field_settings[ $key ] = $value;

		}

		update_post_meta( $this->get_object_id(), '_field_settings', $field_settings );

	}

}
