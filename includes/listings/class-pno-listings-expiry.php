<?php
/**
 * Handles expiration of listings.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles registration of expiration fields and actions that expire listings.
 */
class PNO_Listings_Expiry {

	/**
	 * Get things started.
	 *
	 * @return void
	 */
	public function init() {

		// Stop everything if expiration is disabled.
		if ( ! pno_listings_can_expire() ) {
			add_filter( 'pno_listings_table_columns', [ $this, 'disable_dashboard_expiry_column' ] );
			return;
		}

	}

	/**
	 * Disable expiry column within the dashboard table.
	 *
	 * @param array $cols registered columns.
	 * @return array
	 */
	public function disable_dashboard_expiry_column( $cols ) {
		if ( isset( $cols['expires'] ) ) {
			unset( $cols['expires'] );
		}
		return $cols;
	}

}

( new PNO_Listings_Expiry() )->init();
