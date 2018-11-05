<?php
/**
 * Representation of a listing opening hours field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class responsible of handling listings opening hours within a PNO\Form.
 */
class ListingOpeningHoursField extends AbstractGroup {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		parent::init();
		$this->set_type( 'listing-opening-hours' );
		return $this->set_value( $this->get_option( 'value', [] ) );
	}

	/**
	 * Bind the value of the field.
	 *
	 * @param string $value the value of the field.
	 * @return $this the current object.
	 */
	public function bind( $value ) {
		if ( $value ) {
			$value            = json_decode( wp_unslash( $value ) );
			$redefined_value  = new \stdClass();
			$days_of_the_week = pno_get_days_of_the_week();
			foreach ( $days_of_the_week as $day => $day_name ) {
				if ( isset( $value->{$day} ) ) {
					$operation_type                   = $value->{$day}->type;
					$hours                            = $value->{$day}->hours;
					$redefined_value->$day            = new \stdClass();
					$redefined_value->$day->operation = $operation_type;
					$redefined_value->$day->opening   = $hours[0]->opening;
					$redefined_value->$day->closing   = $hours[0]->closing;
					unset( $hours[0] );
					if ( is_array( $hours ) && ! empty( $hours ) ) {
						foreach ( $hours as $additional_time_slot ) {
							$redefined_value->$day->additional_times[] = $additional_time_slot;
						}
					}
				}
			}
			$value = $redefined_value;
			return $this->set_value( wp_json_encode( $value ) );
		}
		return $this->set_value( array() );
	}

}
