<?php
/**
 * List of actions that belong to listings.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Modify the amount of listings displayed on each page.
 *
 * @param object $query current query.
 * @return void
 */
function pno_adjust_listings_posts_per_page( $query ) {

	$is_listing_page = false;
	$listings_taxonomies = get_object_taxonomies( 'listings' );

	if ( is_post_type_archive( 'listings' ) ) {
		$is_listing_page = true;
	} elseif ( $query->is_tax() ) {
		$current_tax = get_queried_object();
		if ( $current_tax instanceof WP_Term && isset( $current_tax->taxonomy ) && in_array( $current_tax->taxonomy, $listings_taxonomies ) ) {
			$is_listing_page = true;
		}
	}

	if ( ! is_admin() && $query->is_main_query() && $is_listing_page ) {
		$query->set( 'posts_per_page', pno_get_listings_results_per_page_active_option() );
	}
}
add_action( 'pre_get_posts', 'pno_adjust_listings_posts_per_page' );
