<?php
/**
 * This template displays a single location within the listings locations shortcode, specifically
 * only when the location does not have a featured image.
 *
 * This template can be overridden by copying it to yourtheme/posterno/shortcodes/locations/simple-list.php
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

$listing_location  = $data->listing_location;
$show_sublocations = $data->show_sublocations;

?>

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

						$listings_found = absint( $child_location->count );

						if ( $listings_found <= 0 ) {
							continue;
						}

						?>
						<li class="d-flex justify-content-between align-items-center mb-1">
							<a href="<?php echo esc_url( get_term_link( $child_location ) ); ?>">
								<?php echo esc_html( $child_location->name ); ?>
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
