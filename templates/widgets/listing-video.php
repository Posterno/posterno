<?php
/**
 * The template for displaying the content of the listing video widget.
 *
 * This template can be overridden by copying it to yourtheme/posterno/widgets/listing-video.php
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

$listing_id    = get_queried_object_id();
$listing_media = carbon_get_post_meta( $listing_id, 'listing_media_embed' );

if ( ! $listing_media ) {

	posterno()->templates
		->set_template_data(
			[
				'type'    => 'info',
				'message' => esc_html__( 'This listing does not have any video attached.', 'posterno' ),
			]
		)
		->get_template_part( 'message' );
		return;

}

?>

<div class="embed-container">
	<?php
		//phpcs:ignore
		echo wp_oembed_get( $listing_media );
	?>
</div>
