<?php
/**
 * The template for displaying the content of the single listing page template.
 *
 * This template can be overridden by copying it to yourtheme/pno/single.php
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

$listing_id = get_the_id();
$categories = pno_get_listing_categories( $listing_id );
$locations  = pno_get_listing_locations( $listing_id );
$gallery    = pno_get_listing_media_items( $listing_id );

// If a gallery is availabe, let's add the featured image too to the list.
if ( ! empty( $gallery ) ) {
	array_unshift( $gallery, get_post_thumbnail_id( $listing_id ) );
}

/**
 * Hook: triggers before the content of the single listing page is displayed.
 */
do_action( 'pno_before_single_listing' );

?>

<div class="pno-single-listing-wrapper">

	<?php if ( pno_listing_is_featured( $listing_id ) ) : ?>
		<span class="badge badge-pill badge-warning featured-badge mb-3"><?php esc_html_e( 'Featured' ); ?></span>
	<?php endif; ?>

	<?php if ( ! empty( $categories ) && is_array( $categories ) ) : ?>
		<nav aria-label="breadcrumb" class="listing-terms-list">
			<ol class="breadcrumb mb-2 mx-0">
				<li>
					<i class="fas fa-folder-open"></i>
				</li>
				<?php foreach ( $categories as $category ) : ?>
					<li class="breadcrumb-item"><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>

	<?php if ( ! empty( $locations ) && is_array( $locations ) ) : ?>
		<nav aria-label="breadcrumb" class="listing-terms-list">
			<ol class="breadcrumb mb-2 mx-0">
				<li>
					<i class="fas fa-map-marker-alt"></i>
				</li>
				<?php foreach ( $locations as $location ) : ?>
					<li class="breadcrumb-item"><a href="<?php echo esc_url( get_term_link( $location ) ); ?>"><?php echo esc_html( $location->name ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>

	<?php if ( has_post_thumbnail() && ! is_array( $gallery ) || ( has_post_thumbnail() && empty( $gallery ) ) ) : ?>
		<div class="listing-featured-image">
			<?php the_post_thumbnail( 'full' ); ?>
		</div>
	<?php endif; ?>

	<?php
	// Load the listings gallery.
	if ( ! empty( $gallery ) && is_array( $gallery ) ) {
		posterno()->templates
			->set_template_data(
				[
					'items' => $gallery,
				]
			)
			->get_template_part( 'listings/gallery' );
	}

	?>

	<div class="mt-4">
		<?php the_content(); ?>
	</div>

	<div class="listing-meta-fields">

		<ul class="list-group">
			<li class="list-group-item"><span class="field-title">Field</span>: value</li>
		</ul>

	</div>

</div>

<?php

/**
 * Hook: triggers after the content of the single listing page is displayed.
 */
do_action( 'pno_after_single_listing' );
