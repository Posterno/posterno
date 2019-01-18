<?php
/**
 * The template for displaying the images gallery within the single listing page template.
 *
 * This template can be overridden by copying it to yourtheme/pno/listings/gallery.php
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

$items  = $data->items;
$images = [];

foreach ( $items as $item_id ) {
	$attachment_url = wp_get_attachment_url( $item_id );
	if ( ! empty( $attachment_url ) ) {
		$extension = substr( strrchr( $attachment_url, '.' ), 1 );
		if ( 'image' === wp_ext2type( $extension ) ) {
			$images[] = $attachment_url;
		}
	}
}

if ( empty( $images ) ) {
	return;
}

?>

<div id="pno-single-listing-gallery" class="carousel slide" data-ride="carousel">

	<ol class="carousel-indicators">
		<?php foreach ( $images as $key => $image_url ) : ?>
			<li data-target="#pno-single-listing-gallery" data-slide-to="<?php echo absint( $key ); ?>" class="<?php if ( absint( $key ) === 0 ) : ?>active<?php endif; ?>"></li>
		<?php endforeach; ?>
	</ol>

	<div class="carousel-inner">
		<?php foreach ( $images as $key => $image_url ) : ?>
			<div class="carousel-item <?php if ( absint( $key ) === 0 ) : ?>active<?php endif; ?>">
				<img src="<?php echo esc_url( $image_url ); ?>" class="d-block w-100" alt="...">
			</div>
		<?php endforeach; ?>
	</div>

	<a class="carousel-control-prev" href="#pno-single-listing-gallery" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only">Previous</span>
	</a>
	<a class="carousel-control-next" href="#pno-single-listing-gallery" role="button" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only">Next</span>
	</a>

</div>
