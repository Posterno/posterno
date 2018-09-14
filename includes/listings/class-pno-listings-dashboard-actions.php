<?php
/**
 * Handles execution of actions that are triggered via the dashboard page for listings.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Trigger actions for listings from the dashboard.
 */
class PNO_Listings_Dashboard_Actions {

	/**
	 * Hook into WordPress and process the actions.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'delete_listing' ] );
	}

	/**
	 * Delete a listing from the site when the user deletes it from his account through the dashboard.
	 *
	 * @return void
	 */
	public static function delete_listing() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! isset( $_GET['listing_action'] ) ) {
			return;
		}

		if ( ! isset( $_GET['listing_id'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'verify_listing_action' ) ) {
			return;
		}

		if ( isset( $_GET['listing_action'] ) && $_GET['listing_action'] !== 'delete' ) {
			return;
		}

		$listing_id = absint( $_GET['listing_id'] );
		$user_id    = get_current_user_id();

		// Verify the currently logged in user is the author of the listing being deleted.
		$listing = get_post( $listing_id );

		if ( $listing instanceof WP_Post && $listing->post_author == $user_id ) {
			pno_delete_listing( $listing->ID );
		}

		// Redirect the user back to the listings management page.
		$redirect = pno_get_dashboard_navigation_item_url( 'listings' );
		$redirect = add_query_arg( [ 'message' => 'listing-deleted' ], $redirect );
		wp_safe_redirect( $redirect );
		exit;

	}

}

( new PNO_Listings_Dashboard_Actions() )->init();
