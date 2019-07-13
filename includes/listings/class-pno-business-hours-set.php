<?php
/**
 * Representation of a listing's business hours set.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Listing\BusinessHours;

use DateTime;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Represents a listing's business hours set for a day.
 */
class Set {

	/**
	 * Opening time.
	 *
	 * @var DateTime|null
	 */
	public $start;

	/**
	 * Closing time.
	 *
	 * @var DateTime|null
	 */
	public $end;

	/**
	 * Whether it closes after midnight.
	 *
	 * @var boolean
	 */
	public $after_midnight = false;

	/**
	 * Human readable formatted opening time.
	 *
	 * @var string
	 */
	public $start_time;

	/**
	 * Human readable formatted closing time.
	 *
	 * @var string
	 */
	public $end_time;

	/**
	 * Numeric representation of the day of the week.
	 *
	 * @var string|int
	 */
	public $day_of_week;

	/**
	 * The name of the day.
	 *
	 * @var string
	 */
	public $day_name;

	/**
	 * Holds the type of hours set. IE: hours, open all day, closed all day, appointment etc..
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Flag to determine if this set belongs to today's date.
	 *
	 * @var boolean
	 */
	public $is_today = false;

	/**
	 * List of additional sets for this day.
	 *
	 * @var array
	 */
	public $additional_sets = [];

	/**
	 * Get things started.
	 *
	 * @param DateTime       $start opening time.
	 * @param DateTime       $end closing time.
	 * @param string|boolean $type the type of operation in place for the day.
	 */
	public function __construct( DateTime $start = null, DateTime $end = null, $type = false ) {

		$this->start = $start;
		$this->end   = $end;

		$this->type = $type;

		if ( $this->start && $this->end ) {

			$this->day_of_week = (int) $this->start->format( 'N' );

			$format         = pno_get_option( 'business_hours_abbr', false ) ? 'D' : 'l';
			$this->day_name = date_i18n( $format, strtotime( $this->start->format( 'l' ) ) );

			$this->start_time = $this->start->format( 'g:i A' );
			$this->end_time   = $this->end->format( 'g:i A' );

			// Reset properties of the set if the timeset type does not have hours.
			if ( $this->type !== 'hours' ) {
				$this->start      = false;
				$this->end        = false;
				$this->start_time = false;
				$this->end_time   = false;
			}
		}

	}

	/**
	 * Verify if two sets of opening hours are equal.
	 *
	 * @param Set     $opening_hours set to compare.
	 * @param boolean $including_day whether to include the day or not into the comparison.
	 * @return boolean
	 */
	public function is_equal( Set $opening_hours, $including_day = false ) {

		if ( $including_day && $this->day_of_week !== $opening_hours->day_of_week ) {
			return false;
		}

		return $this->start_time === $opening_hours->start_time && $this->end_time === $opening_hours->end_time;

	}

	/**
	 * Print the business hours set on the frontend.
	 *
	 * @param string $format custom format.
	 * @param string $separator custom separator.
	 * @return string
	 */
	public function to_string( $format = 'g:i A', $separator = ' &mdash; ' ) {

		if ( ! $this->start || ! $this->end ) {
			if ( $this->type && array_key_exists( $this->type, pno_get_listing_time_slots() ) ) {
				$timeslots = pno_get_listing_time_slots();
				return $timeslots[ $this->type ];
			} else {
				return;
			}
		}

		if ( pno_get_option( 'business_hours_24h' ) ) {
			$format = 'H:i';
		}

		$start = $this->start->format( $format );
		$end   = $this->end->format( $format );

		$remove_zeroes = pno_get_option( 'business_hours_remove_zeroes', false );

		if ( $remove_zeroes ) {
			$start = str_replace( ':00', '', $start );
			$end   = str_replace( ':00', '', $end );
		}

		if ( $this->has_additional_sets() ) {
			foreach ( $this->additional_sets as $set ) {
				$end .= '<br/>';
				$set_start = $set->start->format( $format );
				$set_end   = $set->end->format( $format );

				if ( $remove_zeroes ) {
					$set_start = str_replace( ':00', '', $set_start );
					$set_end   = str_replace( ':00', '', $set_end );
				}

				$end .= sprintf(
					'%s%s%s',
					$set_start,
					$separator,
					$set_end
				);
			}
		}

		return sprintf(
			'%s%s%s',
			$start,
			$separator,
			$end
		);

	}

	/**
	 * Determine if this set belongs to today's date.
	 *
	 * @return boolean
	 */
	public function is_today() {
		return (bool) $this->is_today;
	}

	/**
	 * Retrieve the name of the day assigned to the set.
	 *
	 * @return string
	 */
	public function get_day_name() {
		return $this->day_name;
	}

	/**
	 * Verify if the day has additional timing sets.
	 *
	 * @return boolean
	 */
	public function has_additional_sets() {
		return count( $this->additional_sets ) > 0;
	}

}
