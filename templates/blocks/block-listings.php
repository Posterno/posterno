<?php
/**
 * The template for displaying the listings query block.
 *
 * This template can be overridden by copying it to yourtheme/posterno/blocks/block-listings.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$posts_per_page = isset( $data->number ) ? absint( $data->number ) : absint( pno_get_listings_results_per_page_options() );
$featured       = isset( $data->show_featured_only ) && $data->show_featured_only === true;
$pagination     = isset( $data->pagination ) && $data->pagination === true;
$layout         = isset( $_GET['layout'] ) ? pno_get_listings_results_active_layout() : ( isset( $data->layout ) && array_key_exists( $data->layout, pno_get_listings_layout_options() ) ? $data->layout : pno_get_listings_results_active_layout() );
$author_id      = isset( $data->user_id ) ? absint( $data->user_id ) : false;
$taxonomies     = isset( $data->taxonomies ) && ! empty( $data->taxonomies ) ? pno_clean( explode( ',', $data->taxonomies ) ) : [];
$terms          = isset( $data->terms ) && ! empty( $data->terms ) ? pno_clean( json_decode( $data->terms, true ) ) : [];

$args = [
	'post_type'         => 'listings',
	'pno_search'        => true,
	'is_listings_query' => true,
	'posts_per_page'    => $posts_per_page,
];

// Add pagination support.
if ( $pagination ) {
	$args['paged'] = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
}

// Add featured listings only support to the query.
if ( $featured ) {
	$args['meta_query'] = [
		[
			'key'   => '_listing_is_featured',
			'value' => 'yes',
		],
	];
}

// Add specific author support to the query.
if ( $author_id ) {
	$args['author'] = $author_id;
}

// Add support for taxonomy terms filtering.
if ( is_array( $taxonomies ) && ! empty( $taxonomies ) ) {
	$filter = [];
	foreach ( $taxonomies as $taxonomy_name ) {
		$specific_terms = isset( $terms[ $taxonomy_name ] ) && ! empty( $terms[ $taxonomy_name ] ) ? $terms[ $taxonomy_name ] : false;
		if ( $specific_terms ) {
			$filter[] = [
				'taxonomy' => $taxonomy_name,
				'field'    => 'term_id',
				'terms'    => $specific_terms,
			];
		}
	}
	if ( ! empty( $filter ) ) {
		$args['tax_query'] = $filter;
	}
}

$i = '';

/**
 * Filter: allow developers to modify the WP_Query arguments for listings
 * generated through the listings block.
 *
 * @param array $args WP_Query arguments list.
 * @param object $data attributes sent through the block.
 * @return array
 */
$args = apply_filters( 'pno_listings_block_query', $args, $data );

$query = new WP_Query( $args );

?>

<div class="pno-block-listings-wrapper posterno-template">
	<?php

	if ( $query->have_posts() ) {

		// Start opening the grid's container.
		if ( $layout === 'grid' ) {
			echo '<div class="card-deck">';
		}

		while ( $query->have_posts() ) {

			$query->the_post();

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

		if ( $pagination ) {
			posterno()->templates
				->set_template_data(
					[
						'query' => $query,
					]
				)
				->get_template_part( 'listings/results', 'footer' );
		}
	} else {

		posterno()->templates->get_template_part( 'listings/not-found' );

	}

	?>
</div>
