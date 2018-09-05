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
