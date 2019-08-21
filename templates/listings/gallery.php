<?php
/**
 * The template for displaying the images gallery within the single listing page template.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/gallery.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.1
 * @package posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$items  = $data->items;
$images = [];

foreach ( $items as $item_id ) {
	if ( is_array( $item_id ) && isset( $item_id['url'] ) ) {
		$images[] = $item_id['url'];
	}
}

if ( empty( $images ) ) {
	return;
}

if ( isset( $data->featured_image ) && ! empty( $data->featured_image ) ) {
	array_unshift( $images, $data->featured_image );
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
				<img src="<?php echo esc_url( $image_url ); ?>" class="d-block w-100" alt="<?php the_title(); ?>">
			</div>
		<?php endforeach; ?>
	</div>

	<a class="carousel-control-prev" href="#pno-single-listing-gallery" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only"><?php esc_html_e( 'Previous', 'posterno' ); ?></span>
	</a>
	<a class="carousel-control-next" href="#pno-single-listing-gallery" role="button" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only"><?php esc_html_e( 'Next', 'posterno' ); ?></span>
	</a>

</div>
