<?php
/**
 * The template for displaying the submitted posts component content on profile pages.
 *
 * This template can be overridden by copying it to yourtheme/posterno/profile/posts.php
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
	'post_type'   => 'post',
	'post_status' => 'publish',
	'author'      => $data->user_id,
	'paged'       => $is_paged,
];

$user_posts = new WP_Query( $args );

?>

<div id="pno-profile-posts" class="mt-4">

	<?php

	if ( $user_posts->have_posts() ) {

		$placeholder_enabled = pno_is_listing_placeholder_image_enabled();

		echo '<ul class="list-unstyled">';

		while ( $user_posts->have_posts() ) {

			$user_posts->the_post();

			$featured_img = get_the_post_thumbnail_url( get_the_id(), false );

			?>

			<li class="media mb-4">

				<?php if ( has_post_thumbnail() || $placeholder_enabled ) : ?>

					<div class="pno-post-img-wrapper">
						<a href="<?php the_permalink(); ?>">
							<?php

							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'small', [ 'class' => 'mr-3' ] );
							} else {
								echo '<img src="' . esc_url( pno_get_listing_placeholder_image() ) . '" alt="' . get_the_title() . '" class="mr-3">';
							}

							?>
						</a>
					</div>

				<?php endif; ?>

				<div class="media-body">
					<h5 class="mt-0 mb-1">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h5>
					<?php
					if ( ! empty( get_the_excerpt() ) ) {
						the_excerpt();
					}
					?>
					<a href="<?php the_permalink(); ?>" class="btn btn-secondary btn-sm"><?php esc_html_e( 'Read more', 'posterno' ); ?></a>
				</div>
			</li>

			<?php

		}

		echo '</ul>';

		// Display pagination.
		posterno()->templates
			->set_template_data( [ 'max_num_pages' => $user_posts->max_num_pages ] )
			->get_template_part( 'listings/pagination' );

	} else {

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'info',
					'message' => sprintf( esc_html__( 'No posts have been submitted by %s', 'posterno' ), pno_get_user_first_name( $data->user_details ) ),
				]
			)
			->get_template_part( 'message' );

	}

	wp_reset_postdata();

	?>

</div>
