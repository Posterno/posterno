<?php
/**
 * The template for displaying the content of the recent listings page, this is like a copy of the taxonomy page.
 *
 * This template can be overridden by copying it to yourtheme/posterno/shortcodes/listings-page.php
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

// Determine the currently active listings layout.
$layout = pno_get_listings_results_active_layout();
$i      = '';

$args = [
	'post_type'         => 'listings',
	'is_listings_query' => true,
];

$query = new WP_Query( $args );

/**
 * Hook: loads before the content of the listings page shortcode.
 *
 * @param WP_Query $query the query loaded for the shortcode.
 */
do_action( 'pno_before_listings_page', $query );

?>

<div class="pno-taxonomy-wrapper">

	<div class="pno-listings-container">

		<?php

		if ( $query->have_posts() ) {

			posterno()->templates
				->set_template_data(
					[
						'custom_query' => $query,
					]
				)
				->get_template_part( 'listings/results', 'bar' );

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

			posterno()->templates->get_template_part( 'listings/results', 'footer' );

		} else {

			posterno()->templates->get_template_part( 'listings/not-found' );

		}

		?>

	</div>

</div>

<?php

/**
 * Hook: loads after the content of the listings page shortcode.
 *
 * @param WP_Query $query the query loaded for the shortcode.
 */
do_action( 'pno_after_listings_page', $query );
