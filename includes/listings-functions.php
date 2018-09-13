<?php
/**
 * All listings related functionalities of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve a list of registered social medias for Posterno.
 *
 * @return array
 */
function pno_get_listings_registered_social_media() {

	$socials = [
		'facebook'       => esc_html__( 'Facebook' ),
		'twitter'        => esc_html__( 'Twitter' ),
		'google-plus'    => esc_html__( 'Google+' ),
		'linkedin'       => esc_html__( 'LinkedIn' ),
		'pinterest'      => esc_html__( 'Pinterest' ),
		'instagram'      => esc_html__( 'Instagram' ),
		'tumblr'         => esc_html__( 'Tumblr' ),
		'flickr'         => esc_html__( 'Flickr' ),
		'snapchat'       => esc_html__( 'Snapchat' ),
		'reddit'         => esc_html__( 'Reddit' ),
		'youtube'        => esc_html__( 'Youtube' ),
		'vimeo'          => esc_html__( 'Vimeo' ),
		'github'         => esc_html__( 'Github' ),
		'dribbble'       => esc_html__( 'Dribbble' ),
		'behance'        => esc_html__( 'Behance' ),
		'soundcloud'     => esc_html__( 'SoundCloud' ),
		'stack-overflow' => esc_html__( 'Stack Overflow' ),
		'whatsapp'       => esc_html__( 'Whatsapp' ),
	];

	/**
	 * Allows developers to register additional social media networks for listings.
	 *
	 * @param array $socials the current list of social medias.
	 * @return array
	 */
	$socials = apply_filters( 'pno_registered_social_media', $socials );

	asort( $socials );

	return $socials;

}

/**
 * Get timings for the opening hours selector.
 *
 * @return array
 */
function pno_get_am_pm_declaration() {

	$timings = [
		'am' => esc_html__( 'AM' ),
		'pm' => esc_html__( 'PM' ),
	];

	return $timings;
}

/**
 * Retrieve an array with the days of the week.
 *
 * @return array
 */
function pno_get_days_of_the_week() {

	$days = [
		'monday'    => esc_html__( 'Monday' ),
		'tuesday'   => esc_html__( 'Tuesday' ),
		'wednesday' => esc_html__( 'Wednesday' ),
		'thursday'  => esc_html__( 'Thursday' ),
		'friday'    => esc_html__( 'Friday' ),
		'saturday'  => esc_html__( 'Saturday' ),
		'sunday'    => esc_html__( 'Sunday' ),
	];

	return $days;

}

/**
 * Store opening hours of a given listing by day into the database.
 *
 * @param mixed  $listing_id ID of the listing to update.
 * @param string $day string of the day we're saving @see pno_get_days_of_the_week().
 * @param string $slot opening or closing.
 * @param string $time submitted time.
 * @return void
 */
function pno_update_listing_opening_hours_by_day( $listing_id = false, $day = '', $slot = '', $time = '' ) {

	if ( ! $listing_id || empty( $day ) || empty( $slot ) ) {
		return;
	}

	if ( ! array_key_exists( $day, pno_get_days_of_the_week() ) ) {
		return;
	}

	$existing_timings = get_post_meta( $listing_id, '_listing_opening_hours', true );

	if ( empty( $existing_timings ) || ! is_array( $existing_timings ) ) {
		$existing_timings = [];
	}

	$slots = [
		'opening',
		'closing',
	];

	if ( ! in_array( $slot, $slots ) ) {
		return;
	}

	// If we receive an empty time, then we remove the slot from the storage array.
	if ( empty( $time ) ) {
		if ( isset( $existing_timings[ $day ][ $slot ] ) ) {
			unset( $existing_timings[ $day ][ $slot ] );
		}
	} else {

		$time = is_array( $time ) ? $time : sanitize_text_field( $time );

		$existing_timings[ $day ][ $slot ] = $time;

	}

	update_post_meta( $listing_id, '_listing_opening_hours', $existing_timings );

}

/**
 * Store additional opening hours for a specific day of the week for listings.
 *
 * @param mixed  $listing_id the listing id.
 * @param string $day string of day of the week.
 * @param array  $timings list of closing and opening times.
 * @return void
 */
function pno_update_listing_additional_opening_hours_by_day( $listing_id = false, $day = '', $timings = [] ) {

	if ( ! $listing_id || empty( $day ) ) {
		return;
	}

	if ( ! array_key_exists( $day, pno_get_days_of_the_week() ) ) {
		return;
	}

	$existing_timings = get_post_meta( $listing_id, '_listing_opening_hours', true );

	if ( empty( $existing_timings ) || ! is_array( $existing_timings ) ) {
		return;
	}

	if ( ! isset( $existing_timings[ $day ] ) ) {
		return;
	}

	if ( empty( $timings ) ) {

		if ( isset( $existing_timings[ $day ]['additional_times'] ) ) {
			unset( $existing_timings[ $day ]['additional_times'] );
		}
	} else {
		$existing_timings[ $day ]['additional_times'] = $timings;
	}

	update_post_meta( $listing_id, '_listing_opening_hours', $existing_timings );

}

/**
 * Assign a listing type to a given listing.
 *
 * @param string|int $listing_id the id of the listing.
 * @param string|int $type_id the id of the taxonomy (listing type) that we're going to add.
 * @return void
 */
function pno_assign_type_to_listing( $listing_id, $type_id ) {

	if ( ! $listing_id || ! $type_id ) {
		return;
	}

	wp_set_object_terms( $listing_id, $type_id, 'listings-types' );

}

/**
 * Retrieve the listings submitted by a specific user given a user id.
 *
 * @param string $user_id the user for which we're going to retrieve listings.
 * @return WP_Query
 */
function pno_get_user_submitted_listings( $user_id ) {

	if ( ! $user_id ) {
		return false;
	}

	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

	$query_args = [
		'post_type'   => 'listings',
		'number'      => 10,
		'author'      => absint( $user_id ),
		'post_status' => 'publish',
		'fields'      => 'ids',
		'paged'       => $paged,
	];

	// Detect if a status has been selected within the dashboard page.
	if ( isset( $_GET['listing_status'] ) && ! empty( $_GET['listing_status'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'verify_listings_dashboard_status' ) ) {
		$query_args['post_status'] = pno_get_dashboard_active_listings_status();
	}

	/**
	 * Allow developers to customize the query arguments when
	 * retrieving listings submitted by a specific user.
	 *
	 * @param array $query_args WP_Query args array.
	 * @param string $user_id the id number of the queried user.
	 */
	$query_args = apply_filters( 'pno_user_submitted_listings_query_args', $query_args, $user_id );

	$found_listings = new WP_Query( $query_args );

	return $found_listings;

}

/**
 * Displays the title for the listing.
 *
 * @param int|WP_Post $post listing post object or post id.
 * @return void
 */
function pno_the_listing_title( $post = null ) {
	$listing_title = pno_get_the_listing_title( $post );
	if ( $listing_title ) {
		echo wp_kses_post( $listing_title );
	}
}

/**
 * Gets the title for the listing.
 *
 * @param int|WP_Post $post (default: null).
 * @return string|bool|null
 */
function pno_get_the_listing_title( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'listings' !== $post->post_type ) {
		return null;
	}
	$title = wp_strip_all_tags( get_the_title( $post ) );
	/**
	 * Filter for the listing title.
	 *
	 * @param string      $title Title to be filtered.
	 * @param int|WP_Post $post
	 */
	return apply_filters( 'pno_the_listing_title', $title, $post );
}

/**
 * Displays the published date of the listing.
 *
 * @param int|WP_Post $post (default: null).
 */
function pno_the_listing_publish_date( $post = null ) {
	$date_format = pno_get_option( 'listing_date_format' );
	if ( 'default' === $date_format ) {
		$display_date = date_i18n( get_option( 'date_format' ), get_post_time( 'U', false, $post ) );
	} else {
		// translators: Placeholder %s is the relative, human readable time since the listing listing was posted.
		$display_date = sprintf( esc_html__( 'Posted %s ago' ), human_time_diff( get_post_time( 'U', false, $post ), current_time( 'timestamp' ) ) );
	}
	echo '<time datetime="' . esc_attr( get_post_time( 'Y-m-d', false, $post ) ) . '">' . wp_kses_post( $display_date ) . '</time>';
}

/**
 * Gets the published date of the listing.
 *
 * @param int|WP_Post $post (default: null).
 * @return string|int|false
 */
function pno_get_the_listing_publish_date( $post = null ) {
	$date_format = pno_get_option( 'listing_date_format' );
	if ( 'default' === $date_format ) {
		return get_post_time( get_option( 'date_format' ) );
	} else {
		// translators: Placeholder %s is the relative, human readable time since the listing listing was posted.
		return sprintf( __( 'Posted %s ago' ), human_time_diff( get_post_time( 'U', false, $post ), current_time( 'timestamp' ) ) );
	}
}

/**
 * Displays the expire date of the listing.
 *
 * @param int|WP_Post $post (default: null).
 * @return void
 */
function pno_the_listing_expire_date( $post = null ) {

	$expires = pno_get_the_listing_expire_date( $post ) ? pno_get_the_listing_expire_date( $post ) : '&ndash;';

	echo '<time datetime="' . esc_attr( $expires ) . '">' . wp_kses_post( $expires ) . '</time>';

}

/**
 * Gets the expire date of the listing.
 *
 * @param int|WP_Post $post (default: null).
 * @return string|int|false
 */
function pno_get_the_listing_expire_date( $post = null ) {

	$expires = get_post_meta( $post, 'listing_expires', true );

	return esc_html( $expires ? date_i18n( get_option( 'date_format' ), strtotime( $expires ) ) : false );

}

/**
 * Retrieve the list of registered post statuses for listings,
 * minus the draft status that's not used on the fronted.
 *
 * @return array
 */
function pno_get_dashboard_listings_statuses() {

	$registered_statuses = pno_get_listing_post_statuses();

	if ( isset( $registered_statuses['draft'] ) ) {
		unset( $registered_statuses['draft'] );
	}

	return $registered_statuses;

}

/**
 * Detect the currently active listing status.
 *
 * @return string
 */
function pno_get_dashboard_active_listings_status() {

	$statuses      = pno_get_dashboard_listings_statuses();
	$active_status = 'publish';

	if ( isset( $_GET['listing_status'] ) && ! empty( $_GET['listing_status'] ) ) { //phpcs:ignore
		$status = sanitize_key( $_GET['listing_status'] );
		if ( isset( $statuses[ $status ] ) ) {
			$active_status = $status;
		}
	}

	return $active_status;

}

/**
 * Retrieve the url of a given listing status filter.
 * This is used within the listings dashboard.
 *
 * @param boolean $status_key the post status we're using to filter.
 * @return mixed
 */
function pno_get_dashboard_listing_status_filter_url( $status_key = false ) {

	if ( ! $status_key ) {
		return;
	}

	$url = wp_nonce_url( pno_get_dashboard_navigation_item_url( 'listings' ), 'verify_listings_dashboard_status' );
	$url = add_query_arg(
		[
			'listing_status' => sanitize_key( $status_key ),
		], $url
	);

	return $url;

}
