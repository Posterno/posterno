<?php
/**
 * Custom datastore for storing custom fields details. All data is stored under the "settings" column
 * of the table related to whichever custom field is being saved.
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
class CustomFieldSettings extends Post_Meta_Datastore {

	/**
	 * Determine which type of custom field we're saving
	 * so we can save data to the appropriate table.
	 *
	 * @var boolean|string
	 */
	protected $custom_field_type = false;

	/**
	 * Temporary store the id of newly created settings object id.
	 *
	 * @var integer
	 */
	private $settings_object_id = 0;

	/**
	 * Specify the type of custom field we're going to save.
	 *
	 * @param string $type the type of custom field eg: listing, profile, registration.
	 * @return void
	 */
	public function set_custom_field_type( $type ) {
		if ( $type ) {
			$this->custom_field_type = $type;
		}
	}

	/**
	 * Retrieve the specified type of custom field.
	 *
	 * @return boolean|string
	 */
	private function get_custom_field_type() {
		return $this->custom_field_type;
	}

	/**
	 * Retrieve the appropriate field query object class for the given datastore type.
	 *
	 * @return mixed
	 */
	private function get_field_type_class() {

		$field = false;

		if ( $this->get_custom_field_type() === 'profile' ) {
			$field = new \PNO\Database\Queries\Profile_Fields();
		} elseif ( $this->get_custom_field_type() === 'registration' ) {
			$field = new \PNO\Database\Queries\Registration_Fields();
		} elseif ( $this->get_custom_field_type() === 'listing' ) {
			$field = new \PNO\Database\Queries\Listing_Fields();
		}

		return $field;

	}

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

		$key = $this->get_key_for_field( $field );

		$pno_field      = $this->get_field_type_class();
		$existing_field = $this->get_field_settings();

		$field_settings = [];
		$value          = '';

		if ( $pno_field instanceof $pno_field ) {
			$field_settings = $existing_field;
		}

		if ( is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {

			$value = isset( $field_settings[ $key ] ) ? $field_settings[ $key ] : [];

		} elseif ( is_a( $field, '\\Carbon_Fields\\Field\\Checkbox_Field' ) && isset( $field_settings[ $key ] ) && ! empty( $field_settings[ $key ] ) ) {

			$value = true;

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
		$field_settings = $this->get_field_settings();
		$exists         = true;

		if ( empty( $field_settings ) || ! is_array( $field_settings ) ) {
			$field_settings = [];
			$exists         = false;
		}

		if ( empty( $value ) ) {
			if ( isset( $field_settings[ $key ] ) ) {
				unset( $field_settings[ $key ] );
			}
		} else {
			if ( is_array( $value ) && is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {
				$formatted_options = [];
				foreach ( $value as $optkey => $array_of_options ) {
					$array_of_options             = array_map( 'sanitize_text_field', $array_of_options );
					$formatted_options[ $optkey ] = $array_of_options;
				}
				$value = $formatted_options;
			} else {
				$value = sanitize_text_field( $value );
			}
			$field_settings[ $key ] = $value;
		}

		$this->update_field_settings( $field_settings );

	}

	/**
	 * Retrieve settings stored into the database for the given Posterno's field.
	 * If no field is found into the database, we create a new row automagically.
	 *
	 * @return array
	 */
	private function get_field_settings() {

		$settings = [];

		$field = $this->get_field_type_class();

		$field_exists = $field->get_item_by( 'post_id', $this->get_object_id() );

		if ( $field_exists ) {

			$settings = $field_exists->get_settings();

		} else {

			// Automatically create a new row into the database when no settings are found.
			if ( $this->settings_object_id ) {
				return;
			}

			$new_field = $field->add_item( [ 'post_id' => $this->get_object_id() ] );

			$this->settings_object_id = $new_field;

		}

		return $settings;

	}

	/**
	 * Update settings for the given Posterno's field into the database.
	 *
	 * @param array $settings the settings to update.
	 * @return void
	 */
	private function update_field_settings( $settings ) {

		$field = $this->get_field_type_class();

		$settings_object_id = $field->get_item_by( 'post_id', $this->get_object_id() );

		$data_to_save = [
			'settings' => maybe_serialize( $settings ),
		];

		if ( $this->get_custom_field_type() === 'profile' && isset( $settings['_profile_field_meta_key'] ) ) {
			$data_to_save['user_meta_key'] = $settings['_profile_field_meta_key'];
		}

		$field->update_item( $settings_object_id, $data_to_save );

	}

}
