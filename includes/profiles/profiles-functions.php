<?php
/**
 * List of functions used for profiles.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get the currently queried user's id for the profile page.
 *
 * @return string
 */
function pno_get_queried_user_id() {

	$user_id = get_current_user_id();

	return $user_id;

}

/**
 * Get the user's full name. Returns display name if no first name is found.
 *
 * @param mixed $user_id_or_object the user's to analyze.
 * @return string
 */
function pno_get_user_fullname( $user_id_or_object = false ) {

	if ( ! $user_id_or_object ) {
		return;
	}

	$user_info = $user_id_or_object instanceof WP_User ? $user_id_or_object : get_userdata( $user_id );

	if ( $user_info->first_name ) {

		if ( $user_info->last_name ) {
			return $user_info->first_name . ' ' . $user_info->last_name;
		}

		return $user_info->first_name;
	}

	return $user_info->display_name;

}

/**
 * Retrieve the list of available navigation items for the profile page.
 *
 * @return array
 */
function pno_get_profile_components() {

	$items = [
		'about'    => esc_html__( 'About' ),
		'posts'    => esc_html__( 'Posts' ),
		'listings' => esc_html__( 'Listings' ),
		'comments' => esc_html__( 'Comments' ),
	];

	/**
	 * Filter: adjust the list of available navigation items for the profile page.
	 *
	 * @param array $items the currently registered list of items.
	 * @return array
	 */
	return apply_filters( 'pno_profile_components', $items );

}
