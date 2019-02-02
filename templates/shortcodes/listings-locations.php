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
		'taxonomy'   => 'listings-locations',
		'hide_empty' => false,
		'parent'     => 0,
	)
);

$i = 0;

if ( ! is_array( $terms ) || empty( $terms ) ) {
	return;
}

$show_sublocations = isset( $data->sublocations ) && $data->sublocations === 'yes' ? true : false;

?>

<div class="pno-listings-terms-list">

	<div class="row">

	<?php foreach ( $terms as $listing_location ) : ?>

		<div class="col-md-4">

			<ul class="list-unstyled m-0 mb-3">
				<li>
					<a href="<?php echo esc_url( get_term_link( $listing_location ) ); ?>" class="d-block mb-2 parent-term">
						<strong><?php echo esc_html( $listing_location->name ); ?></strong>

						<?php if ( isset( $listing_location->count ) && absint( $listing_location->count ) > 0 ) : ?>
							<span class="badge badge-pill badge-secondary ml-2"><?php echo absint( $listing_location->count ); ?></span>
						<?php endif; ?>
					</a>

					<?php

					if ( $show_sublocations ) {

						$children = get_term_children( $listing_location->term_id, 'listings-locations' );

						if ( ! empty( $children ) && is_array( $children ) ) {

							echo '<ul class="list-unstyled m-0">';

							foreach ( $children as $child_term_id ) {

								$child_location = get_term_by( 'id', absint( $child_term_id ), 'listings-locations' );

								if ( $child_location instanceof WP_Term ) :
									?>
										<li class="d-flex justify-content-between align-items-center mb-1">
											<a href="<?php echo esc_url( get_term_link( $child_location ) ); ?>">
												<?php echo esc_html( $child_location->name ); ?>
											</a>
											<?php if ( isset( $child_location->count ) && absint( $child_location->count ) > 0 ) : ?>
												<span class="badge badge-pill badge-light"><?php echo absint( $child_location->count ); ?></span>
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

