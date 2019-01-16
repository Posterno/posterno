<?php
/**
 * The template for displaying the submitted listings component content on profile pages.
 *
 * This template can be overridden by copying it to yourtheme/pno/profile/listings.php
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

$is_paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$args = [
	'post_type'      => 'listings',
	'post_status'    => 'publish',
	'posts_per_page' => pno_get_option( 'listings_per_page', 10 ),
	'author'         => $data->user_id,
	'paged'          => $is_paged,
];

$user_listings = new WP_Query( $args );

?>

<div id="pno-profile-listings" class="mt-4">

	<?php if ( $user_listings->have_posts() ) : ?>

		<?php

		while ( $user_listings->have_posts() ) {

			$user_listings->the_post();

			posterno()->templates->get_template_part( 'listings/card', 'list' );

		}

		// Display pagination.
		posterno()->templates
			->set_template_data( [ 'max_num_pages' => $user_listings->max_num_pages ] )
			->get_template_part( 'listings/pagination' );

		?>

	<?php else : ?>

		<?php

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'info',
					'message' => sprintf( esc_html__( 'No listings have been submitted by %s' ), pno_get_user_first_name( $data->user_details ) ),
				]
			)
			->get_template_part( 'message' );

		?>

	<?php

	endif;

	wp_reset_postdata();

?>

</div>
