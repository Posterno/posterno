<?php
/**
 * The template for displaying the content of the listings categories list shortcode.
 *
 * This template can be overridden by copying it to yourtheme/posterno/shortcodes/listings-categories.php
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
		'hide_empty' => true,
		'parent'     => 0,
	)
);

$i = 0;

if ( ! is_array( $terms ) || empty( $terms ) ) {
	return;
}

$show_subcategories = isset( $data->subcategories ) && $data->subcategories === 'yes' ? true : false;

?>

<div class="pno-listings-terms-list">

	<div class="row">

	<?php

	foreach ( $terms as $listing_category ) :

		$icon = carbon_get_term_meta( $listing_category->term_id, 'term_icon' );

	?>

		<div class="col-md-4">

			<ul class="list-unstyled m-0 mb-3">
				<li>
					<a href="<?php echo esc_url( get_term_link( $listing_category ) ); ?>" class="d-block mb-2 parent-term">
						<?php if ( $icon ) : ?>
							<span class="term-icon rounded-circle">
								<i class="<?php echo esc_attr( $icon ); ?>"></i>
							</span>
						<?php endif; ?>
						<strong><?php echo esc_html( $listing_category->name ); ?></strong>
						<?php if ( isset( $listing_category->count ) && absint( $listing_category->count ) > 0 ) : ?>
							<span class="badge badge-pill badge-secondary ml-2"><?php echo absint( $listing_category->count ); ?></span>
						<?php endif; ?>
					</a>

					<?php

					if ( $show_subcategories ) {

						$children = get_term_children( $listing_category->term_id, 'listings-categories' );

						if ( ! empty( $children ) && is_array( $children ) ) {

							echo '<ul class="list-unstyled m-0">';

							foreach ( $children as $child_term_id ) {

								$child_category = get_term_by( 'id', absint( $child_term_id ), 'listings-categories' );

								if ( $child_category instanceof WP_Term ) :

									$listings_found = absint( $child_category->count );

									if ( $listings_found <= 0 ) {
										continue;
									}

									?>
										<li class="d-flex justify-content-between align-items-center mb-1">
											<a href="<?php echo esc_url( get_term_link( $child_category ) ); ?>">
												<?php echo esc_html( $child_category->name ); ?>
											</a>
											<?php if ( $listings_found > 0 ) : ?>
												<span class="badge badge-pill badge-light"><?php echo absint( $listings_found ); ?></span>
											<?php endif; ?>
										</li>
									<?php
								endif;

							}

							echo '</ul>';

						}

					}

					?>

				</li>
			</ul>

		</div>

	<?php endforeach; ?>

	</div>

</div>

