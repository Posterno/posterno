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
 * Adjust different parameters of listings queries.
 *
 * - on archives and taxonomies set the selected posts_per_page parameter.
 * - on archives and taxonomies set the selected listings sort order.
 *
 * @param object $query current query.
 * @return void
 */
function pno_adjust_listings_query( $query ) {

	$is_listing_page     = false;
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

		// Set posts per page.
		$query->set( 'posts_per_page', pno_get_listings_results_per_page_active_option() );

		// Set sorting order.
		$active_order = pno_get_listings_results_order_active_filter();

		if ( $active_order ) {

			$orderby = 'date';
			$order   = 'DESC';

			switch ( $active_order ) {
				case 'newest':
					$orderby = 'date';
					$order   = 'DESC';
					break;
				case 'oldest':
					$orderby = 'date';
					$order   = 'ASC';
					break;
				case 'title':
					$orderby = 'title';
					$order   = 'ASC';
					break;
				case 'random':
					$orderby = 'rand';
					break;
			}

			$query->set( 'orderby', $orderby );
			$query->set( 'order', $order );

		}
	}
}
add_action( 'pre_get_posts', 'pno_adjust_listings_query' );
