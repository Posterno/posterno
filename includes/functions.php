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
 * @return string|boolean
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
 * Retrieve the id number of the selected listing editing page in the admin panel.
 *
 * @return string|boolean
 */
function pno_get_listing_editing_page_id() {

	$listing_submission_page = false;
	$page_option             = pno_get_option( 'editing_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$listing_submission_page = absint( $page_option['value'] );
	}

	return $listing_submission_page;

}

/**
 * Retrieve the id number of the selected success page to display to users
 * after they've successfully submitted a listing.
 *
 * @return mixed
 */
function pno_get_listing_success_redirect_page_id() {

	$page_id     = false;
	$page_option = pno_get_option( 'listing_submission_redirect' );
	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$page_id = absint( $page_option['value'] );
	}

	return $page_id;

}

/**
 * Retrieve the id number of the selected success page to display to users
 * after they've successfully edited a listing.
 *
 * @return mixed
 */
function pno_get_listing_success_edit_redirect_page_id() {

	$page_id     = false;
	$page_option = pno_get_option( 'listing_editing_redirect' );
	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$page_id = absint( $page_option['value'] );
	}

	return $page_id;

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

/**
 * Retrieve the queried listing type id during the submission process.
 *
 * @return mixed
 */
function pno_get_submission_queried_listing_type_id() {

	return isset( $_GET['listing_type'] ) && ! empty( sanitize_text_field( $_GET['listing_type'] ) ) ? absint( $_GET['listing_type'] ) : false;

}

/**
 * Retrieve the list of categories for the listings submission form.
 *
 * @param mixed $listing_type_id query categories by associated listing type id.
 * @return array
 */
function pno_get_listings_categories_for_submission_selection( $listing_type_id = false ) {

	$categories = [];

	$categories_associated_to_type = carbon_get_term_meta( $listing_type_id, 'associated_categories' );
	$show_subcategories            = pno_get_option( 'submission_categories_sublevel' ) ? true : false;

	$terms_args = array(
		'hide_empty' => false,
		'number'     => 999,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'parent'     => 0,
	);

	if ( $listing_type_id && pno_get_option( 'submission_categories_associated' ) ) {
		$terms_args['include'] = $categories_associated_to_type;
	}

	if ( $show_subcategories ) {

		$parent_terms = get_terms( 'listings-categories', $terms_args );

		if ( ! empty( $parent_terms ) && is_array( $parent_terms ) ) {
			foreach ( $parent_terms as $parent_listing_category ) {

				$category_group = [
					'parent_id'   => $parent_listing_category->term_id,
					'parent_name' => $parent_listing_category->name,
				];

				// Now query for subcategories.
				$sub_terms_args = array(
					'hide_empty' => false,
					'number'     => 999,
					'orderby'    => 'name',
					'order'      => 'ASC',
					'parent'     => $parent_listing_category->term_id,
				);
				$subcategories  = get_terms( 'listings-categories', $sub_terms_args );

				if ( ! empty( $subcategories ) && is_array( $subcategories ) ) {
					foreach ( $subcategories as $subcategory ) {
						$category_group['subcategories'][] = [
							'id'   => $subcategory->term_id,
							'name' => $subcategory->name,
						];
					}

					$categories[] = $category_group;

				}
			}
		}
	} else {

		$terms = get_terms( 'listings-categories', $terms_args );

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $listing_category ) {
				$categories[ absint( $listing_category->term_id ) ] = esc_html( $listing_category->name );
			}
		}
	}

	return $categories;

}

/**
 * Retrieve the most parent term of a given term.
 *
 * @param string $term_id the id of the term to verify.
 * @param string $taxonomy the taxonomy of the term to verify.
 * @return mixed
 */
function pno_get_term_top_most_parent( $term_id, $taxonomy ) {

	// start from the current term.
	$parent = get_term_by( 'id', $term_id, $taxonomy );

	// climb up the hierarchy until we reach a term with parent = '0'.
	while ( $parent->parent != '0' ) {
		$term_id = $parent->parent;
		$parent  = get_term_by( 'id', $term_id, $taxonomy );
	}

	return $parent;
}

/**
 * Retrieve the list of registered map providers in Posterno.
 *
 * @return array
 */
function pno_get_registered_maps_providers() {

	$providers = [
		'googlemaps' => esc_html__( 'Google maps' ),
	];

	/**
	 * Allow developers to customize the registered maps providers for Posterno.
	 *
	 * @param array $provides the list of currently registered providers.
	 * @return array
	 */
	return apply_filters( 'pno_registered_maps_providers', $providers );

}

/**
 * Determine if a user has submitted listings.
 *
 * @param string $user_id the user to check.
 * @return mixed
 */
function pno_user_has_submitted_listings( $user_id ) {

	global $wpdb;

	if ( ! $user_id ) {
		return;
	}

	$user_id = absint( $user_id );

	return wp_cache_remember(
		"user_has_submitted_listings_{$user_id}", function () use ( $wpdb, $user_id ) {
			$where = "WHERE ( ( post_type = 'listings' AND ( post_status = 'publish' OR post_status = 'pending' OR post_status = 'expired' ) ) ) AND post_author = {$user_id}";
			$count = $wpdb->get_var("SELECT ID FROM $wpdb->posts $where LIMIT 1"); //phpcs:ignore
			return $count;
		}
	);

}
