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
	 * Get things started.
	 *
	 * @param string|int $listing_id the listing id for which we're checking business hours.
	 * @throws Exception When no listing id is provided.
	 */
	public function __construct( $listing_id ) {

		if ( ! $listing_id ) {
			throw new Exception( 'Invalid listing id.' );
		}

		$this->listing_id    = absint( $listing_id );
		$this->opening_hours = $this->get_opening_hours();

	}

	/**
	 * Get the listing's opening hours from the database.
	 *
	 * @return array
	 */
	public function get_opening_hours() {

		$stored_hours = get_post_meta( $this->listing_id, '_listing_opening_hours', true );

		if ( ! is_array( $stored_hours ) ) {
			$stored_hours = [];
		}

		return $stored_hours;

	}

	/**
	 * Get the listing's opening hours on a specific date.
	 *
	 * @param DateTime $datetime the date to check.
	 * @return void
	 */
	public function get_opening_hours_on( DateTime $datetime ) {

	}

	/**
	 * Verify if the listing is currently open.
	 *
	 * @return boolean
	 */
	public function is_open() {

	}

	/**
	 * Verify if the listing is currently closed.
	 *
	 * @return boolean
	 */
	public function is_closed() {

	}

}
