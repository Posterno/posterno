<?php
/**
 * The template for displaying the maps infowindow.
 *
 * This template can be overridden by copying it to yourtheme/posterno/maps/marker-infowindow.php
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

$listing_id = absint( $data->listing_id );

$featured_image = get_the_post_thumbnail_url( $listing_id, 'full' );

?>

<div class="card" style="max-width: 200px;">
	<?php if ( $featured_image ) : ?>
		<a href="<?php echo esc_url( get_the_permalink( $listing_id ) ); ?>">
			<img src="<?php echo esc_url( $featured_image ); ?>" class="card-img-top" alt="<?php echo esc_attr( get_the_title( $listing_id ) ); ?>">
		</a>
	<?php endif; ?>
	<div class="card-body">
		<h5 class="card-title">
			<a href="<?php echo esc_url( get_the_permalink( $listing_id ) ); ?>">
				<?php echo esc_html( get_the_title( $listing_id ) ); ?>
			</a>
		</h5>
		<p class="card-text"><?php echo esc_html( get_the_excerpt( $listing_id ) ); ?></p>
	</div>
</div>
