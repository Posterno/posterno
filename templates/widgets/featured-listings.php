<?php
/**
 * The template for displaying the content of the featured listings widget.
 *
 * This template can be overridden by copying it to yourtheme/pno/widgets/featured-listings.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 * @package posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$sorters = [
	'menu_order' => 'ASC',
	'date'       => 'DESC',
];

$args = [
	'post_type'      => 'listings',
	'posts_per_page' => isset( $data->number ) ? absint( $data->number ) : '10',
	'orderby'        => $sorters,
	'meta_query'     => array(
		array(
			'key'   => '_listing_is_featured',
			'value' => 'yes',
		),
	),
];

$layout = isset( $data->layout ) ? $data->layout : 'grid';

$listings = new WP_Query( $args );

if ( $listings->have_posts() ) {

	/**
	 * Hook: loads before the featured listings loop when listings are available.
	 */
	do_action( 'pno_before_featured_listings_loop' );

	while ( $listings->have_posts() ) {

		/**
		 * Hook: loads before the content of a single listing is loaded within the loop of the featured listings widget.
		 */
		do_action( 'pno_before_listing_in_featured_listings_loop' );

		$listings->the_post();

		posterno()->templates->get_template_part( 'listings/card', $layout );

		/**
		 * Hook: loads after the content of a single listing is loaded within the loop of the featured listings widget.
		 */
		do_action( 'pno_after_listing_in_featured_listings_loop' );

	}

	/**
	 * Hook: loads after the featured listings loop when listings are available.
	 */
	do_action( 'pno_after_featured_listings_loop' );

} else {

	posterno()->templates
		->set_template_data(
			[
				'type'    => 'info',
				'message' => esc_html__( 'No listings have been found.' ),
			]
		)
		->get_template_part( 'message' );
		return;

}

wp_reset_postdata();
