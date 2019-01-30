<?php
/**
 * Handles the api to read opening hours assigned to listings.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Listing;

use PNO\Listing\BusinessHours\Set;
use Rarst\WordPress\DateTime\WpDateTimeZone;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles the api to read and opening hours assigned to listings.
 */
class BusinessHours {

	/**
	 * The listing for which we're checking the opening hours.
	 *
	 * @var integer
	 */
	public $listing_id = 0;

	/**
	 * Business hours stored into the database for the queried listing.
	 *
	 * @var array
	 */
	protected $opening_hours = [];

	/**
	 * Selected timezone.
	 *
	 * @var string
	 */
	protected $timezone = null;

	/**
	 * Whether the week starts on a sunday instead of monday.
	 *
	 * @var boolean
	 */
	protected $week_starts_on_sunday = false;

	/**
	 * List of days of the week with sunday as first day.
	 *
	 * @var array
	 */
	protected $days_of_the_week_sunday_first = [];

	/**
	 * Get things started.
	 *
	 * @param string|int $listing_id the listing id for which we're checking business hours.
	 * @throws \Exception When no listing id is provided.
	 */
	public function __construct( $listing_id ) {

		if ( ! $listing_id ) {
			throw new \Exception( 'Invalid listing id.' );
		}

		$this->listing_id                    = absint( $listing_id );
		$this->opening_hours                 = $this->find_opening_hours();
		$this->timezone                      = WpDateTimeZone::getWpTimezone()->getName();
		$this->week_starts_on_sunday         = pno_get_option( 'business_hours_sunday_start', false );
		$days_of_the_week                    = pno_get_days_of_the_week();
		$this->days_of_the_week_sunday_first = array( 'sunday' => $days_of_the_week['sunday'] ) + $days_of_the_week;

	}

	/**
	 * Find the listing's opening hours from the database.
	 *
	 * @return array
	 */
	public function find_opening_hours() {

		$stored_hours = get_post_meta( $this->listing_id, '_listing_opening_hours', true );

		if ( ! is_array( $stored_hours ) ) {
			$stored_hours = [];
		}

		return $stored_hours;

	}

	/**
	 * Get the listing's opening hours sets.
	 *
	 * @return array
	 */
	public function get_opening_hours() {

		$sets = [];

		foreach ( $this->opening_hours as $day => $raw_opening_hours ) {

			print_r( $raw_opening_hours );

			$operation = isset( $raw_opening_hours['operation'] ) ? $raw_opening_hours['operation'] : false;
			$sets[]    = $this->raw_hour_to_opening_hours( $raw_opening_hours, $day, $operation );

			$additional_times = isset( $raw_opening_hours['additional_times'] ) && ! empty( $raw_opening_hours['additional_times'] ) ? $raw_opening_hours['additional_times'] : false;

			if ( $additional_times && is_array( $additional_times ) ) {
				foreach ( $additional_times as $timeset ) {

					$opening = isset( $timeset['opening'] ) ? $timeset['opening'] : false;
					$closing = isset( $timeset['closing'] ) ? $timeset['closing'] : false;

					if ( $opening && $closing ) {

						$formatted_timeset = [
							'opening' => $opening,
							'closing' => $closing,
						];

						$sets[] = $this->raw_hour_to_opening_hours( $formatted_timeset, $day, $operation );

					}
				}
			}
		}

		return $sets;

	}

	/**
	 * Get the listing's opening hours on a specific date.
	 *
	 * @param \DateTime $datetime the date to check.
	 * @return void
	 */
	public function get_opening_hours_on( \DateTime $datetime ) {

	}

	/**
	 * Verify if the listing is currently open.
	 *
	 * @return boolean
	 */
	public function is_currently_open() {

	}

	/**
	 * Verify if the listing is currently closed.
	 *
	 * @return boolean
	 */
	public function is_currently_closed() {

	}

	/**
	 * Get today's date, time and timezone.
	 *
	 * @param boolean $include_time whether to include time or not.
	 * @return \DateTime date object.
	 */
	public function get_now( $include_time = true ) {

		$zone = new \DateTimeZone( $this->timezone );
		$date = new \DateTime( 'now', $zone );

		if ( ! $include_time ) {
			$date->setTime( 0, 0, 0 );
		}

		return $date;

	}

	/**
	 * Get today's day as a number.
	 *
	 * @return int
	 */
	public function get_today() {

		$zone = new \DateTimeZone( $this->timezone );
		$now  = new \DateTime( 'now', $zone );

		if ( $this->week_starts_on_sunday ) {
			return (int) $now->format( 'w' ) + 1;
		}

		return (int) $now->format( 'N' );

	}

	/**
	 * Get difference between 2 dates.
	 *
	 * @param \DateTime $date first date to verify.
	 * @param \DateTime $date2 second date to verify.
	 * @param string    $type the type of difference.
	 * @return string
	 */
	public function get_difference( \DateTime $date, \DateTime $date2, $type = 'seconds' ) {

		if ( $type === 'days' ) {

			$d1 = clone $date;
			$d1->setTime( 0, 0, 0 );
			$d2 = clone $date2;
			$d2->setTime( 0, 0, 0 );

			$interval = $d1->diff( $d2 );

			return $interval->days;

		}

		$interval = $date->diff( $date2 );

		switch ( $type ) {
			case 'seconds':
				return $interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
			case 'minutes':
				return $interval->days * 1440 + $interval->h * 60 + $interval->i;
			case 'hours':
				return $interval->days * 24 + $interval->h;
		}

	}

	/**
	 * Retrieve the difference between 2 dates as a translatable human readable string.
	 *
	 * @param \DateTime $date the date against which we're going to get the difference.
	 * @param \DateTime $date2 the date to use as reference for the difference.
	 * @return string
	 */
	public function get_difference_as_translatable_string( \DateTime $date, \DateTime $date2 ) {

		$diff = $date->diff( $date2 );

		if ( $diff->s > 0 ) {
			$diff->i = $diff->i + 1;
		}

		$string = array();

		if ( absint( $diff->y ) > 0 ) {
			array_push( $string, $diff->y . ' ' . __( _n( 'year', 'years', absint( $diff->y ) ) ) );
		}
		if ( absint( $diff->m ) > 0 ) {
			array_push( $string, $diff->m . ' ' . __( _n( 'month', 'months', absint( $diff->m ) ) ) );
		}
		if ( absint( $diff->d ) > 0 ) {
			array_push( $string, $diff->d . ' ' . __( _n( 'day', 'days', absint( $diff->d ) ) ) );
		}
		if ( absint( $diff->h ) > 0 ) {
			array_push( $string, $diff->h . ' ' . __( _n( 'hour', 'hours', absint( $diff->h ) ) ) );
		}
		if ( absint( $diff->i ) > 0 ) {
			array_push( $string, $diff->i . ' ' . __( _n( 'minute', 'minutes', absint( $diff->i ) ) ) );
		}

		if ( count( $string ) === 0 ) {
			return 1 . ' ' . __( 'minute' );
		}

		return join( ', ', $string );

	}

	/**
	 * Determine if a given date is today's date.
	 *
	 * @param \DateTime $date the date to verify.
	 * @return boolean
	 */
	public function is_today( \DateTime $date ) {

		$zone = $this->timezone;

		return $this->get_difference( $date, $this->get_now(), 'days' ) === 0;

	}

	/**
	 * Get the difference of days between two given days of the week.
	 *
	 * @param string $day name of the day eg: monday, tuesday etc. Must be lowercase.
	 * @param string $other_day name of the day eg: monday, tuesday etc. Must be lowercase.
	 * @return string|int
	 */
	public function get_difference_between_weekdays( $day, $other_day ) {

		$days_of_the_week = pno_get_days_of_the_week();

		$position       = array_search( strtolower( $day ), array_keys( $days_of_the_week ) );
		$other_position = array_search( strtolower( $other_day ), array_keys( $days_of_the_week ) );

		return $other_position - $position;
	}

	/**
	 * Verify if today's date is between two dates. One in the past and one in the future.
	 *
	 * @param \DateTime $start date in the past.
	 * @param \DateTime $end date in the future.
	 * @return boolean
	 */
	public function is_now_between_dates( \DateTime $start, \DateTime $end ) {
		return $start <= $this->get_now() && $end >= $this->get_now();
	}

	/**
	 * Retrieve the name of the day for a given date.
	 *
	 * @param \DateTime $date the date.
	 * @return string
	 */
	public function get_day_of_week_from_date( \DateTime $date ) {
		return $date->format( 'l' );
	}

	/**
	 * Retrieve the numeric value of the day for a given date.
	 *
	 * @param \DateTime $date the date.
	 * @return string
	 */
	public function get_day_of_week_from_date_as_int( \DateTime $date ) {
		return $this->week_starts_on_sunday ? ( $date->format( 'w' ) + 1 ) : $date->format( 'N' );
	}

	/**
	 * Retrieve the shorter form of a given day name.
	 *
	 * @param string $day the name of the day.
	 * @return string
	 */
	public function get_short_day_of_week_from_day( $day ) {

		$days_of_the_week = pno_get_days_of_the_week_short();

		return isset( $days_of_the_week[ $day ] ) ? $days_of_the_week[ $day ] : false;

	}

	/**
	 * Retrieve \DateTime objects for dates of the current week.
	 *
	 * @return array
	 */
	public function get_dates_for_this_week() {

		$dates = array();

		$days_of_the_week = pno_get_days_of_the_week();

		foreach ( ( $this->week_starts_on_sunday ? $this->days_of_the_week_sunday_first : $days_of_the_week ) as $dow => $label ) {
			$dates[] = $this->convert_to_date_in_week( '12AM', $dow, 0 );
		}

		return $dates;

	}

	/**
	 * Convert the a time of a day to a \DateTime object.
	 *
	 * @param string  $time time of the day.
	 * @param string  $day_of_week day string like monday, tuesday etc.
	 * @param integer $offset_in_weeks offset lookup for the time.
	 * @return \DateTime
	 */
	public function convert_to_date_in_week( $time, $day_of_week, $offset_in_weeks = 0 ) {

		$today = $this->get_today();

		$day_as_number = array_search( $day_of_week, $this->week_starts_on_sunday ? array_keys( $this->days_of_the_week_sunday_first ) : array_keys( pno_get_days_of_the_week() ) ) + 1;
		$offset        = ( $day_as_number - $today ) + ( $offset_in_weeks * 7 );
		$zone          = WpDateTimeZone::getWpTimezone();

		$date_time = new \DateTime( $time, $zone );

		if ( $offset === 0 ) {
			return $date_time;
		}

		$interval = new \DateInterval( 'P' . abs( $offset ) . 'D' );

		return $offset > 0 ? $date_time->add( $interval ) : $date_time->sub( $interval );

	}

	/**
	 * Add days to a given date.
	 *
	 * @throws \Exception When no valid number of days is given.
	 * @param \DateTime  $date the date to manipulate.
	 * @param string|int $number_of_days the days to add to the date.
	 * @return \DateTime
	 */
	public function add_days( \DateTime $date, $number_of_days ) {

		if ( ! is_int( $number_of_days ) || ! is_numeric( $number_of_days ) ) {
			throw new \Exception( 'No valid number of days given.' );
		}

		$new = clone $date;

		return $new->add( new \DateInterval( 'P' . abs( $number_of_days ) . 'D' ) );

	}

	/**
	 * Convert a day and a month to a \DateTime object.
	 *
	 * @param string|int $day the numeric value of a calendar day.
	 * @param string     $month the name of the month.
	 * @return \DateTime
	 */
	public function get_day_month_to_date( $day, $month ) {

		$zone = WpDateTimeZone::getWpTimezone();

		return new \DateTime( sprintf( '%s %s', $day, $month ), $zone );

	}

	/**
	 * Convert a day, month time to a \DateTime object.
	 *
	 * @param string|int $day the numeric value of a calendar day.
	 * @param string     $month the name of the month.
	 * @param string     $time time.
	 * @param string     $time_indication time indication.
	 * @return \DateTime
	 */
	public function get_day_month_to_datetime( $day, $month, $time, $time_indication ) {

		$zone = WpDateTimeZone::getWpTimezone();

		return \DateTime::createFromFormat( 'M-j h:i a', $month . '-' . $day . ' ' . $time . ' ' . strtolower( $time_indication ), $zone );

	}

	/**
	 * Convert a day, month, time to a \DateTime object.
	 *
	 * @param string|int $day the numeric value of a calendar day.
	 * @param string     $month the name of the month.
	 * @param string     $time time.
	 * @param string     $time_indication time indication.
	 * @return \DateTime
	 */
	public function to_datetime( $day, $month, $time, $time_indication = 'AM' ) {

		$zone = WpDateTimeZone::getWpTimezone();

		return new \DateTime(
			sprintf(
				'%s %s, %s%s',
				$day,
				$month,
				$time,
				$time_indication
			),
			$zone
		);

	}

	/**
	 * Create the business hours set.
	 *
	 * @param \DateTime $start_time starting time.
	 * @param \DateTime $end_time closing time.
	 * @param boolean   $type type of operation in place for the day.
	 * @return Set
	 */
	private function start_and_end_to_opening_hours( \DateTime $start_time, \DateTime $end_time, $type = false ) {

		$after_midnight = false;

		if ( $end_time <= $start_time ) {
			$end_time       = $this->add_days( $end_time, 1 );
			$after_midnight = true;
		}

		$hours                 = new Set( $start_time, $end_time, $type );
		$hours->after_midnight = $after_midnight;

		if ( $this->is_today( $start_time ) ) {
			$hours->is_today = true;
		}

		return $hours;

	}

	/**
	 * Convert raw time sets to date time objects.
	 *
	 * @param string $raw_hours business hours of the timeset.
	 * @param string $dayname the name of the day.
	 * @param string $type the type of operation the business is using with that time set. Eg: hours, open all day, appointment, etc.
	 * @return array
	 */
	private function raw_hour_to_opening_hours( $raw_hours, $dayname, $type = false ) {

		$opening = isset( $raw_hours['opening'] ) ? $raw_hours['opening'] : false;
		$closing = isset( $raw_hours['closing'] ) ? $raw_hours['closing'] : false;

		$start_time = $this->convert_to_date_in_week( $opening, $dayname, 0 );
		$end_time   = $this->convert_to_date_in_week( $closing, $dayname, 0 );

		if ( $type === 'hours' && $opening === false || ( $type === 'hours' && $closing === false ) ) {
			$type = false;
		}

		$hours = $this->start_and_end_to_opening_hours( $start_time, $end_time, $type );

		return $hours;

	}

	/**
	 * Retrieve the opening hours set for today's date.
	 *
	 * @return array
	 */
	public function get_opening_hours_of_today() {

		return wp_filter_object_list( $this->get_opening_hours(), [ 'is_today' => true ] );

	}

}
