<?php

/**
 * The template for displaying a listing card within the list layout.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/card-list.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.1.0
 * @package posterno
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Allow addons to bypass the output of card's layout.
 *
 * @param bool $bypass whether or not the output should be stopped.
 * @param string $layout the type of card layout being processed.
 * @return bool
 */
$bypass_layout = apply_filters('pno_bypass_card_layout', false, 'list');

if ($bypass_layout) {
	return;
}

$featured_img = get_the_post_thumbnail_url(get_the_id(), false);

$address      = pno_get_listing_address();
$phone_number = pno_get_listing_phone_number();
$tags         = pno_get_listing_tags();

$placeholder_enabled = pno_is_listing_placeholder_image_enabled();

/**
 * Filter: allow developers to inject a list of additional details within the listing's card layout.
 *
 * @param array $items list of items.
 * @param string $listing_id id number of the listing.
 * @param string $layout the card layout currently being used.
 */
$card_items = apply_filters('pno_listing_card_details', [], get_the_id(), 'list');

?>

<div <?php pno_listing_class( 'card mb-4 pno-listing-card list-template' ); ?>>
	<div class="row no-gutters">
		<div class="col-md-4 listing-img-wrapper">
			<?php if ( $featured_img ) : ?>
				<?php if ( pno_listing_is_featured( get_the_id() ) ) : ?>
					<span class="badge badge-pill badge-warning featured-badge position-absolute ml-3 mt-3"><?php esc_html_e( 'Featured', 'posterno' ); ?></span>
				<?php endif; ?>
				<?php the_post_thumbnail( 'full', [ 'class' => 'card-img h-100 w-100' ] ); ?>
				<a href="#" class="stretched-link"></a>
			<?php else : ?>
				<img src="<?php echo esc_url(pno_get_listing_placeholder_image()); ?>" class="card-img h-100 w-100">
			<?php endif; ?>
		</div>
		<div class="col-md-8">
			<div class="card-body">
				<h4 class="card-title mb-3">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h4>
				<div class="card-text">
					<?php if ( ! empty( $card_items ) && is_array( $card_items ) ) : ?>
						<ul class="list-inline">
							<?php foreach ( $card_items as $card_item ) : ?>
								<li class="list-inline-item"><?php echo $card_item; //phpcs:ignore ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( is_array( $tags ) && ! empty( $tags ) ) : ?>
						<?php foreach ( $tags as $listing_tag ) : ?>
							<a href="<?php echo esc_url( get_term_link( $listing_tag ) ); ?>" class="mb-2 mr-2 badge badge-secondary"><?php echo esc_html( $listing_tag->name ); ?></a>
						<?php endforeach; ?>
					<?php endif; ?>
					<ul>
						<?php if ( $address && isset( $address['address'] ) && ! empty( $address['address'] ) ) : ?>
							<li><i class="fas fa-map-marker-alt mr-2"></i> <?php echo esc_html( $address['address'] ); ?></li>
						<?php endif; ?>
						<?php if ( $phone_number ) : ?>
							<li><i class="fas fa-phone mr-2"></i> <a href="tel:<?php echo esc_attr( $phone_number ); ?>"><?php echo esc_html( $phone_number ); ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
