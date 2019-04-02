<?php
/**
 * Cron job: check for expired listings and mark them as expired.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Cron;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Check for expired listings and mark them as expired.
 * If enabled, listings that have been expired for more than 30 days will be deleted.
 *
 * @return void
 */
function check_for_expired_listings() {

	global $wpdb;

	if ( ! pno_listings_can_expire() ) {
		return;
	}

	// Change status to expired.
	$listings_ids = $wpdb->get_col(
		$wpdb->prepare(
			"
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID
			WHERE postmeta.meta_key = '_listing_expires'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND posts.post_status = 'publish'
			AND posts.post_type = 'listings'",
			date( 'Y-m-d', current_time( 'timestamp' ) )
		)
	);

	if ( $listings_ids ) {
		foreach ( $listings_ids as $listings_id ) {
			$job_data                = array();
			$job_data['ID']          = $listings_id;
			$job_data['post_status'] = 'expired';
			wp_update_post( $job_data );

			$author_id = pno_get_listing_author( $listings_id );

			if ( $author_id ) {

				$user = get_user_by( 'id', $author_id );

				if ( isset( $user->user_email ) ) {
					pno_send_email(
						'core_listing_expired',
						$user->user_email,
						[
							'user_id'    => $author_id,
							'listing_id' => $listings_id,
						]
					);
				}
			}
		}
	}

	// Delete old expired listings.
	if ( pno_get_option( 'delete_expired_listings', false ) ) {

		/**
		 * Filter: amount of days after which listings are deleted.
		 *
		 * @param int $days_threshold the number of days after which listings are deleted.
		 */
		$days_threshold = apply_filters( 'pno_delete_expired_listings_days', 30 );

		/**
		 * Filter: determine whether or not to permanently delete listings after they've
		 * been expire for longer than 30 days or just move them to the trash.
		 *
		 * @param boolean $permanent true or false.
		 * @return boolean
		 */
		$permanent = apply_filters( 'pno_permanently_delete_expired_listings', false );

		$listings_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT posts.ID FROM {$wpdb->posts} as posts
					WHERE posts.post_type = 'listings'
					AND posts.post_modified < %s
					AND posts.post_status = 'expired'",
				date( 'Y-m-d', strtotime( '-' . absint( $days_threshold ) . ' days', current_time( 'timestamp' ) ) )
			)
		);
		if ( $listings_ids ) {
			foreach ( $listings_ids as $listings_id ) {
				wp_delete_post( $listings_id, $permanent );
			}
		}
	}

}
add_action( 'posterno_check_for_expired_listings', __NAMESPACE__ . '\\check_for_expired_listings' );
