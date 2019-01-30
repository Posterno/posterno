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

class Set {

	public $start;

	public $end;

	public $after_midnight;

	public $start_time;

	public $end_time;

	public $day_of_week;

	public $day_name;

	public function __construct( DateTime $start = null, DateTime $end = null ) {

		$this->start = $start;
		$this->end   = $end;

		if ( $start != null & $end != null ) {

			$this->day_of_week = (int) $this->start->format( 'N' );
			$this->day_name    = $this->start->format( 'l' );
			$this->start_time  = $this->start->format( 'g:i A' );
			$this->end_time    = $this->end->format( 'g:i A' );

		}

	}

	public function is_equal( Set $opening_hours, $including_day = false ) {

		if ( $including_day && $this->day_of_week != $opening_hours->day_of_week ) {
			return false;
		}

		return $this->start_time === $opening_hours->start_time && $this->end_time === $opening_hours->end_time;

	}

	public function to_string( $format = 'g:i A', $separator = ' &mdash; ' ) {

		$start = $this->start->format( $format );
		$end   = $this->end->format( $format );

		if ( pno_get_option( 'business_hours_remove_zeroes' ) ) {
			$start = str_replace( ':00', '', $start );
			$end   = str_replace( ':00', '', $end );
		}

		return sprintf(
			'%s%s%s',
			$start,
			$separator,
			$end
		);

	}

}
