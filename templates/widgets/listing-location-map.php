<?php
/**
 * The template for displaying the content of the listing location map widget.
 *
 * This template can be overridden by copying it to yourtheme/pno/widgets/listing-location-map.php
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

$listing_id = get_queried_object_id();
$coordinates = pno_get_listing_coordinates( $listing_id );

$lat = isset( $coordinates['lat'] ) && ! empty( $coordinates['lat'] ) ? $coordinates['lat'] : false;
$lng = isset( $coordinates['lng'] ) && ! empty( $coordinates['lng'] ) ? $coordinates['lng'] : false;

?>

<pno-single-listing-map inline-template lat="<?php echo esc_attr( $lat ); ?>" lng="<?php echo esc_attr( $lng ); ?>">
	<div class="single-map-wrapper">
		<div class="pno-single-listing-map"></div>
	</div>
</pno-single-listing-map>
