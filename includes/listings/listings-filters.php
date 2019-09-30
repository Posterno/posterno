<?php
/**
 * List of filters that belong to listings.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display the featured when badge into the details list.
 *
 * @param array $items items for the list.
 * @return array
 */
function pno_display_single_listing_featured_badge( $items ) {

	if ( pno_listing_is_featured( get_queried_object_id() ) ) {
		$items[] = '<span class="badge badge-pill badge-warning featured-badge mb-3">' . esc_html__( 'Featured', 'posterno' ) . '</span>';
	}

	return $items;

}
add_filter( 'pno_single_listing_details_list', 'pno_display_single_listing_featured_badge' );
