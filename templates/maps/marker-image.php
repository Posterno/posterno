<?php
/**
 * The template for displaying the maps marker.
 *
 * This template can be overridden by copying it to yourtheme/posterno/maps/marker-image.php
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

// Determine what field the user has chosen.
$image_field = pno_get_option( 'marker_category_image_field', 'listing_featured_image' );

$image = false;

if ( $image_field === 'listing_featured_image' ) {
	$image = get_the_post_thumbnail_url( $listing_id );
} elseif ( $image_field === 'listing_gallery' ) {
	$image = get_post_meta( $listing_id, '_listing_gallery_images', true );
} else {
	$image = carbon_get_post_meta( $listing_id, $image_field );
}

if ( is_array( $image ) ) {
	if ( isset( $image[0] ) ) {
		$image = isset( $image[0]['value'] ) ? $image[0]['value'] : $image[0];
	}
}

if ( is_numeric( $image ) ) {
	$image = wp_get_attachment_url( $image );
}

?>
<div class="pno-map-marker marker-image">
	<div>
		<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $listing_id ) ); ?>">
	</div>
</div>
