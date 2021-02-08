<?php
/**
 * The template for displaying the content of the recent listings shortcode.
 *
 * This template can be overridden by copying it to yourtheme/posterno/shortcodes/recent-listings.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.1
 * @package posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$max    = isset( $data->max ) ? absint( $data->max ) : 10;
$layout = isset( $data->layout ) ? $data->layout : 'grid';
$i      = '';

$args = [
	'post_type'      => 'listings',
	'posts_per_page' => $max,
	'pno_search'     => true,
];

$recent_listings = new WP_Query( $args );

if ( $recent_listings->have_posts() ) {

	// Start opening the grid's container.
	if ( $layout === 'grid' ) {
		echo '<div class="card-deck">';
	}

	while ( $recent_listings->have_posts() ) {

		$recent_listings->the_post();

		/**
		 * Hook: loads before the content of a single listing is loaded within the loop.
		 */
		do_action( 'pno_before_listing_in_loop' );

		posterno()->templates->get_template_part( 'listings/card', $layout );

		// Continue the loop of grids containers.
		if ( $layout === 'grid' ) {
			$i++;
			if ( $i % 3 == 0 ) {
				echo '</div><div class="card-deck">';
			}
		}
	}

	// Close grid's container.
	if ( $layout === 'grid' ) {
		echo '</div>';
	}
}

wp_reset_postdata();
