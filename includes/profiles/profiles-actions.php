<?php
/**
 * List of actions triggered on profile pages.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Displays the content of the "about" component on profile pages.
 *
 * @param string|int $user_id the queried user's id.
 * @param WP_User    $user_details some details about the user.
 * @return void
 */
function pno_profile_about_page( $user_id, $user_details ) {

	if ( ! $user_id ) {
		return;
	}

	posterno()->templates
		->set_template_data(
			[
				'user_id'      => $user_id,
				'user_details' => $user_details,
			]
		)
		->get_template_part( 'profile/about' );

}
add_action( 'pno_profile_content_slot_about', 'pno_profile_about_page', 10, 2 );

/**
 * Displays the content of the "posts" component on profile pages.
 *
 * @param string|int $user_id the queried user's id.
 * @param WP_User    $user_details some details about the user.
 * @return void
 */
function pno_profile_posts_page( $user_id, $user_details ) {

	if ( ! $user_id ) {
		return;
	}

	posterno()->templates
		->set_template_data(
			[
				'user_id'      => $user_id,
				'user_details' => $user_details,
			]
		)
		->get_template_part( 'profile/posts' );

}
add_action( 'pno_profile_content_slot_posts', 'pno_profile_posts_page', 10, 2 );

/**
 * Displays the content of the "listings" component on profile pages.
 *
 * @param string|int $user_id the queried user's id.
 * @param WP_User    $user_details some details about the user.
 * @return void
 */
function pno_profile_listings_page( $user_id, $user_details ) {

	if ( ! $user_id ) {
		return;
	}

	posterno()->templates
		->set_template_data(
			[
				'user_id'      => $user_id,
				'user_details' => $user_details,
			]
		)
		->get_template_part( 'profile/listings' );

}
add_action( 'pno_profile_content_slot_listings', 'pno_profile_listings_page', 10, 2 );

/**
 * Displays the content of the "comments" component on profile pages.
 *
 * @param string|int $user_id the queried user's id.
 * @param WP_User    $user_details some details about the user.
 * @return void
 */
function pno_profile_comments_page( $user_id, $user_details ) {

	if ( ! $user_id ) {
		return;
	}

	posterno()->templates
		->set_template_data(
			[
				'user_id'      => $user_id,
				'user_details' => $user_details,
			]
		)
		->get_template_part( 'profile/comments' );

}
add_action( 'pno_profile_content_slot_comments', 'pno_profile_comments_page', 10, 2 );

/**
 * Restrict access to profiles from visitors when access is disabled.
 * Redirect visitors trying to access the profile page with no queried users back to the login form.
 *
 * @return void
 */
function pno_restrict_access_to_profiles_from_visitors() {

	$profile_page_id  = pno_get_profile_page_id();
	$visitors_allowed = pno_get_option( 'profiles_allow_guests', false );
	$login_page       = pno_get_login_page_id();

	if ( ! empty( $profile_page_id ) && is_int( $profile_page_id ) && is_page( $profile_page_id ) && ! is_user_logged_in() && ! $visitors_allowed ) {
		if ( $login_page && is_int( $login_page ) ) {
			$login_page = add_query_arg(
				[
					'redirect_to' => rawurlencode( get_pagenum_link() ),
					'restricted'  => true,
					'rpage_id'    => $profile_page_id,
				],
				get_permalink( $login_page )
			);
			wp_safe_redirect( $login_page );
			exit;
		}
	}

	if ( ! empty( $profile_page_id ) && is_int( $profile_page_id ) && is_page( $profile_page_id ) && ! is_user_logged_in() && pno_get_queried_user_id < 1 ) {
		if ( $login_page && is_int( $login_page ) ) {
			wp_safe_redirect( get_permalink( $login_page ) );
			exit;
		}
	}

}
add_action( 'template_redirect', 'pno_restrict_access_to_profiles_from_visitors' );

/**
 * Trigger 404 page when trying to access a profile that does not exist.
 *
 * @return void
 */
function pno_when_profile_not_found() {

	$profile_page_id = pno_get_profile_page_id();

	if ( ! empty( $profile_page_id ) && is_int( $profile_page_id ) && is_page( $profile_page_id ) && ! is_admin() ) {
		$queried_user_id = pno_get_queried_user_id();
		if ( $queried_user_id <= 0 ) {
			$find_user = get_user_by( 'id', $queried_user_id );
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}

}
add_action( 'template_redirect', 'pno_when_profile_not_found' );
