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

// Retrieve details about the listing.
$address         = get_post_meta( $listing_id, '_listing_location_address', true );
$address_lat     = get_post_meta( $listing_id, '_listing_location_lat', true );
$address_lng     = get_post_meta( $listing_id, '_listing_location_lng', true );
$contact_email   = get_post_meta( $listing_id, '_listing_email', true );
$contact_phone   = get_post_meta( $listing_id, '_listing_phone_number', true );
$contact_website = get_post_meta( $listing_id, '_listing_website', true );
$social_networks = carbon_get_post_meta( $listing_id, 'listing_social_profiles' );

/**
 * Hook: triggers before the content of the single listing page is displayed.
 */
do_action( 'pno_before_single_listing' );

?>

<div id="pno-single-listing" class="pno-single-listing-wrapper">

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

	<?php

	/**
	 * Hook: triggers before the contact information of the single listing page is displayed.
	 */
	do_action( 'pno_before_single_listing_contact_information' );

	?>

	<div class="listing-contact-info">

		<h4><?php esc_html_e( 'Contact information' ); ?></h4>

		<div class="row">
			<div class="col-md-6">
				<ul class="list-group list-group-flush m-0">
					<?php if ( $address ) : ?>
						<li class="list-group-item pl-0">
							<i class="fas fa-map-marker-alt mr-2"></i>
							<?php echo esc_html( $address ); ?>
						</li>
					<?php endif; ?>
					<?php if ( $contact_phone ) : ?>
						<li class="list-group-item pl-0">
							<i class="fas fa-phone mr-2"></i>
							<?php echo esc_html( $contact_phone ); ?>
						</li>
					<?php endif; ?>
					<?php if ( $contact_email ) : ?>
						<li class="list-group-item pl-0">
							<i class="fas fa-envelope mr-2"></i>
							<?php pno_display_field_value( 'email', $contact_email ); ?>
						</li>
					<?php endif; ?>
					<?php if ( $contact_website ) : ?>
						<li class="list-group-item pl-0">
							<i class="fas fa-external-link-alt mr-2"></i>
							<?php pno_display_field_value( 'url', $contact_website ); ?>
						</li>
					<?php endif; ?>
					<?php if ( $social_networks ) : ?>
						<li class="list-group-item pl-0">
							<?php

								posterno()->templates
									->set_template_data(
										[
											'networks' => $social_networks,
										]
									)
									->get_template_part( 'fields-output/social-networks-field' );

							?>
						</li>
					<?php endif; ?>
				</ul>
			</div>

			<div class="col-md-6">
				<pno-single-listing-map inline-template lat="<?php echo esc_attr( $address_lat ); ?>" lng="<?php echo esc_attr( $address_lng ); ?>">
					<div class="single-map-wrapper">
						<div class="pno-single-listing-map"></div>
					</div>
				</pno-single-listing-map>
			</div>
		</div>

	</div>

	<?php

	/**
	 * Hook: triggers after the contact information of the single listing page is displayed.
	 */
	do_action( 'pno_after_single_listing_contact_information' );

	?>

</div>

<?php

/**
 * Hook: triggers after the content of the single listing page is displayed.
 */
do_action( 'pno_after_single_listing' );
