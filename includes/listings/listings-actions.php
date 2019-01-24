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

		// Prepare orderby parameters and check if listings are featured.
		$listings_can_be_featured  = pno_listings_can_be_featured();
		$enabled_featuring_sorters = pno_get_option( 'listings_featured_in_sorters', [] );

		if ( $active_order ) {

			switch ( $active_order ) {
				case 'newest':
					$sorters = [
						'menu_order' => 'ASC',
						'date'       => 'DESC',
					];
					break;
				case 'oldest':
					$sorters = [
						'menu_order' => 'ASC',
						'date'       => 'ASC',
					];
					break;
				case 'title':
					$sorters = [
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					];
					break;
				case 'title_za':
					$sorters = [
						'menu_order' => 'ASC',
						'title'      => 'DESC',
					];
					break;
				case 'random':
					$sorters = 'rand';
					break;
			}

			/**
			 * Filter: adjust the value for the orderby query parameter when sorting listings.
			 *
			 * @param array $sorters the currently active sorters.
			 * @param string $active_order the currently active order.
			 * @return array
			 */
			$sorters = apply_filters( 'pno_pre_get_posts_active_sorters', $sorters, $active_order );

			if ( isset( $sorters ) && ! empty( $sorters ) ) {
				if ( ! in_array( $active_order, $enabled_featuring_sorters ) ) {
					unset( $sorters['menu_order'] );
				}
				if ( ! $listings_can_be_featured && isset( $sorters['menu_order'] ) ) {
					unset( $sorters['menu_order'] );
				}
				$query->set( 'orderby', $sorters );
			}
		}
	}
}
add_action( 'pre_get_posts', 'pno_adjust_listings_query' );
