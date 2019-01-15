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

	$queried_user   = get_query_var( 'profile_id', false );
	$permalink_base = pno_get_option( 'profile_permalink' );
	$user_id        = false;

	if ( ! empty( $queried_user ) && is_numeric( $queried_user ) ) {
		$user_id = $queried_user;
	} elseif ( ! empty( $queried_user ) && ! is_numeric( $queried_user ) && $permalink_base === 'username' ) {
		$user = get_user_by( 'login', $queried_user );
		if ( $user instanceof WP_User ) {
			$user_id = $user->data->ID;
		}
	} elseif ( empty( $queried_user ) ) {
		$user_id = get_current_user_id();
	}

	return absint( $user_id );

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

/**
 * Get the currently active profile component.
 *
 * @param array $components_menu list of menu items defined into the admin panel.
 * @return string
 */
function pno_get_profile_currently_active_component( $components_menu ) {

	$components        = pno_get_profile_components();
	$queried_component = get_query_var( 'profile_component', false );
	$active_component  = false;

	if ( empty( $queried_component ) ) {

		reset( $components_menu );
		$first            = $components_menu[ key( $components_menu ) ];
		$active_component = isset( $first->post_name ) ? $first->post_name : false;

	} else {
		if ( array_key_exists( $queried_component, $components ) ) {
			$active_component = $queried_component;
		}
	}

	return $active_component;

}

/**
 * Get the url of a profile component.
 *
 * @param string $component the name of the component.
 * @return string
 */
function pno_get_profile_component_url( $component ) {

	$components = pno_get_profile_components();

	if ( ! array_key_exists( $component, $components ) ) {
		return false;
	}

	$profile_page_id  = pno_get_profile_page_id();
	$profile_page_url = get_permalink( $profile_page_id );
	$profile_page_url = trailingslashit( $profile_page_url );
	$profile_page_url = $profile_page_url . pno_get_queried_user_id() . '/' . $component;

	return trailingslashit( $profile_page_url );

}
