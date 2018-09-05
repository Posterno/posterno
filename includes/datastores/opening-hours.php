<?php
/**
 * Custom datastore for listings metadata.
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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manage storage of opening hours of listings.
 */
class OpeningHours extends Post_Meta_Datastore {

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

		$key                = $this->get_key_for_field( $field );
		$timings            = get_post_meta( $this->get_object_id(), '_listing_opening_hours', true );
		$day_string         = $this->get_submitted_day( $key );
		$opening_or_closing = $this->is_opening_or_closing( $key );
		$value              = '';

		if ( is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {

			$value = [];

			if ( isset( $timings[ $day_string ]['additional_times'] ) && is_array( $timings[ $day_string ]['additional_times'] ) ) {

				foreach ( $timings[ $day_string ]['additional_times'] as $key => $timeset ) {

					$openingstring = $day_string . '_opening';
					$closingstring = $day_string . '_closing';

					$value[] = [
						$openingstring => isset( $timeset['opening'] ) ? esc_html( $timeset['opening'] ) : false,
						$closingstring => isset( $timeset['closing'] ) ? esc_html( $timeset['closing'] ) : false,
					];

				}
			}
		} else {

			if ( isset( $timings[ $day_string ] ) && is_array( $timings[ $day_string ] ) ) {
				$opening = isset( $timings[ $day_string ]['opening'] ) ? esc_html( $timings[ $day_string ]['opening'] ) : false;
				$closing = isset( $timings[ $day_string ]['closing'] ) ? esc_html( $timings[ $day_string ]['closing'] ) : false;

				if ( $opening_or_closing == 'opening' ) {
					$value = $opening;
				} elseif ( $opening_or_closing == 'closing' ) {
					$value = $closing;
				}
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

		$key        = $this->get_key_for_field( $field );
		$value      = $field->get_full_value();
		$day_string = $this->get_submitted_day( $key );

		if ( is_a( $field, '\\Carbon_Fields\\Field\\Complex_Field' ) ) {

			$submitted_timings = $field->get_formatted_value();

			$opening_string   = $day_string . '_opening';
			$closing_string   = $day_string . '_closing';
			$additional_times = [];

			foreach ( $submitted_timings as $set => $timeset ) {

				$opening = isset( $timeset[ $opening_string ] ) ? sanitize_text_field( $timeset[ $opening_string ] ) : false;
				$closing = isset( $timeset[ $closing_string ] ) ? sanitize_text_field( $timeset[ $closing_string ] ) : false;

				if ( $opening ) {
					$additional_times[ $set ]['opening'] = $opening;
				}

				if ( $closing ) {
					$additional_times[ $set ]['closing'] = $closing;
				}
			}

			pno_update_listing_additional_opening_hours_by_day( $this->get_object_id(), $day_string, $additional_times );

		} else {

			// Determine the day and if it's the opening or closing time.
			$day_string         = $this->get_submitted_day( $key );
			$opening_or_closing = $this->is_opening_or_closing( $key );
			$submitted_time     = $field->get_formatted_value();

			if ( $day_string && $opening_or_closing && $submitted_time ) {
				pno_update_listing_opening_hours_by_day( $this->get_object_id(), $day_string, $opening_or_closing, $submitted_time );
			}
		}

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
	 * Determine if the field is for the opening or closing.
	 *
	 * @param string $key field key.
	 * @return string
	 */
	private function is_opening_or_closing( $key ) {

		$opening_or_closing = substr( $key, strrpos( $key, '_' ) + 1 );

		return $opening_or_closing;

	}

}
