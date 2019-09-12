<?php
/**
 * Store hours of operation type (all day, closed all day, appointment only, etc...)
 * together with the opening hours metadata.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Datastores;

use Carbon_Fields\Field\Field;
use Carbon_Fields\Datastore\Datastore;
use Carbon_Fields\Datastore\Meta_Datastore;
use Carbon_Fields\Datastore\Post_Meta_Datastore;
use Carbon_Fields\Toolset\Key_Toolset;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Store the type of hours of operation for a given day.
 */
class HoursOfOperation extends Post_Meta_Datastore {

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

		$key     = $this->get_key_for_field( $field );
		$day     = $this->get_submitted_day( $key );
		$timings = get_post_meta( $this->get_object_id(), '_listing_opening_hours', true );

		if ( isset( $timings[ $day ]['operation'] ) && ! empty( $timings[ $day ]['operation'] ) ) {
			return $timings[ $day ]['operation'];
		}

		return true;
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

		$key        = $this->get_key_for_field( $field );
		$value      = $field->get_formatted_value();
		$day_string = $this->get_submitted_day( $key );

		pno_update_listing_hours_of_operation( $this->get_object_id(), $day_string, $value );

	}

	/**
	 * Determine the submitted day.
	 *
	 * @param boolean $key key of the field submitted.
	 * @return mixed
	 */
	private function get_submitted_day( $key = false ) {

		$day_string = false;

		if ( preg_match( '/_(.*?)_/', $key, $match ) == 1 ) {
			$day_string = $match[1];
		}

		return $day_string;

	}

	/**
	 * Delete data associated with the opening hours.
	 *
	 * @param Field $field the field object we're working with.
	 * @return boolean
	 */
	public function delete( Field $field ) {
		return true;
	}

}
