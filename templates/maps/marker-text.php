<?php
/**
 * The template for displaying the maps marker.
 *
 * This template can be overridden by copying it to yourtheme/posterno/maps/marker-text.php
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
$field      = pno_get_option( 'marker_custom_field', false );
$value      = carbon_get_post_meta( $listing_id, $field );

?>
<div class="pno-map-marker marker-text">
	<div class="field-content">
		<?php echo esc_html( $value ); ?>
	</div>
</div>
