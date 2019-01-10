<?php
/**
 * Cron job: check for listings about to expire and send email notifications.
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
 * Check for listings about to expire within a given threshold set in the admin panel
 * and send an email notification to the author and/or administrators.
 *
 * @return void
 */
function notify_of_listings_soon_to_expire() {

	if ( ! pno_listings_can_expire() ) {
		return;
	}

	global $wpdb;

	$email_enabled = pno_get_option( 'listing_expiration_email', false );
	$period        = pno_get_option( 'listing_expiration_email_period', false );

	if ( $email_enabled && $period ) {

		$notice_before_ts = current_time( 'timestamp' ) + ( DAY_IN_SECONDS * absint( $period ) );

		$listings_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID
			WHERE postmeta.meta_key = '_listing_expires'
			AND postmeta.meta_value = %s
			AND posts.post_status = 'publish'
			AND posts.post_type = 'listings'
		",
				date( 'Y-m-d', $notice_before_ts )
			)
		);

		if ( $listings_ids ) {
			foreach ( $listings_ids as $listing_id ) {

				$author_id            = get_post_field( 'post_author', $listing_id );
				$author_email_address = get_the_author_meta( 'user_email', $author_id );

				pno_send_email(
					'core_listing_expiring',
					$author_email_address,
					[
						'user_id'    => $author_id,
						'listing_id' => $listing_id,
					]
				);

			}
		}
	}

}
add_action( 'posterno_email_daily_listings_expiring_notices', __NAMESPACE__ . '\\notify_of_listings_soon_to_expire' );
