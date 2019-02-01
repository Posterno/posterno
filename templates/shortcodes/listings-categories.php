<?php
/**
 * The template for displaying the content of the listings categories list shortcode.
 *
 * This template can be overridden by copying it to yourtheme/pno/shortcodes/listings-categories.php
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

$terms = get_terms(
	array(
		'taxonomy'   => 'listings-categories',
		'hide_empty' => false,
		'parent'     => 0,
	)
);

$i = 0;

if ( ! is_array( $terms ) || empty( $terms ) ) {
	return;
}

?>

<div class="pno-listings-categories-list">

	<div class="card-deck">

		<?php foreach ( $terms as $listing_category ) : ?>

			<ul class="list-unstyled">
				<li>
					<a href="<?php echo esc_url( get_term_link( $listing_category ) ); ?>" class="mb-3">
						<strong><?php echo esc_html( $listing_category->name ); ?></strong>
					</a>

					<?php

					$children = get_term_children( $listing_category->term_id, 'listings-categories' );

					if ( ! empty( $children ) && is_array( $children ) ) {

						echo '<ul class="list-unstyled">';

						foreach ( $children as $child_term_id ) {

							$child_category = get_term_by( 'id', absint( $child_term_id ), 'listings-categories' );

							if ( $child_category instanceof WP_Term ) :
								?>
									<li>
										<a href="<?php echo esc_url( get_term_link( $child_category ) ); ?>">
											<?php echo esc_html( $child_category->name ); ?>
										</a>
									</li>
								<?php
							endif;

						}

						echo '</ul>';

					}

					?>


				</li>
			</ul>

		<?php endforeach; ?>

	</div>

</div>

