<?php
/**
 * List of functions used all around within the plugin.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly..
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the ID number of the selected login page.
 *
 * @return mixed
 */
function pno_get_login_page_id() {

	$login_page  = false;
	$page_option = pno_get_option( 'login_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$login_page = absint( $page_option['value'] );
	}

	return $login_page;

}

/**
 * Retrieve the ID number of the selected registration page.
 *
 * @return mixed
 */
function pno_get_registration_page_id() {

	$registration_page = false;
	$page_option       = pno_get_option( 'registration_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$registration_page = absint( $page_option['value'] );
	}

	return $registration_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_password_recovery_page_id() {

	$password_page = false;
	$page_option   = pno_get_option( 'password_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$password_page = absint( $page_option['value'] );
	}

	return $password_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_dashboard_page_id() {

	$dashboard_page = false;
	$page_option    = pno_get_option( 'dashboard_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$dashboard_page = absint( $page_option['value'] );
	}

	return $dashboard_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_profile_page_id() {

	$profile_page = false;
	$page_option  = pno_get_option( 'profile_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$profile_page = absint( $page_option['value'] );
	}

	return $profile_page;

}

/**
 * Retrieve the id number of the selected listing submission page in the admin panel.
 *
 * @return mixed
 */
function pno_get_listing_submission_page_id() {

	$listing_submission_page = false;
	$page_option             = pno_get_option( 'submission_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$listing_submission_page = absint( $page_option['value'] );
	}

	return $listing_submission_page;
}

/**
 * Defines a list of navigation items for the dashboard page.
 *
 * @return array
 */
function pno_get_dashboard_navigation_items() {

	$items = [
		'dashboard'    => [
			'name' => esc_html__( 'Dashboard' ),
		],
		'listings'     => [
			'name' => esc_html__( 'Manage listings' ),
		],
		'edit-account' => [
			'name' => esc_html__( 'Account details' ),
		],
		'password'     => [
			'name' => esc_html__( 'Change password' ),
		],
		'privacy'      => [
			'name' => esc_html__( 'Privacy settings' ),
		],
		'logout'       => [
			'name' => esc_html__( 'Logout' ),
		],
	];

	/**
	 * Allows developers to register or deregister navigation items
	 * for the dashboard menu.
	 *
	 * @param array $items
	 */
	$items = apply_filters( 'pno_dashboard_navigation_items', $items );

	$first                       = key( $items );
	$items[ $first ]['is_first'] = true;

	return $items;

}

/**
 * Retrieve a list of registered social medias for Posterno.
 *
 * @return array
 */
function pno_get_registered_social_media() {

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
