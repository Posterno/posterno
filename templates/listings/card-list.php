<?php
/**
 * The template for displaying a listing card within the list layout.
 *
 * This template can be overridden by copying it to yourtheme/pno/listings/card-list.php
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

$featured_img = get_the_post_thumbnail_url( get_the_id(), false );

$address      = pno_get_listing_address();
$phone_number = pno_get_listing_phone_number();
$tags         = pno_get_listing_tags();

$placeholder_enabled = pno_is_listing_placeholder_image_enabled();

?>

<?php if ( $featured_img ) : ?>

	<div class="card flex-sm-row mb-4 pno-listing-card list-template">
		<div class="listing-img-wrapper">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'full', [ 'class' => 'card-img-top' ] ); ?>
			</a>
		</div>
		<div class="card-body">
			<h4 class="card-title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h4>
			<div class="card-text">
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

<?php else : ?>

	<div class="card flex-sm-row mb-4 pno-listing-card list-template">
		<?php if ( $placeholder_enabled ) : ?>
			<div class="listing-img-wrapper">
				<a href="<?php the_permalink(); ?>">
					<img src="<?php echo esc_url( pno_get_listing_placeholder_image() ); ?>" alt="<?php the_title(); ?>" class="card-img-top">
				</a>
			</div>
		<?php endif; ?>
		<div class="card-body">
			<h4 class="card-title mb-3">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h4>
			<div class="card-text">
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

<?php endif; ?>