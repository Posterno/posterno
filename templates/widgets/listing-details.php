<?php
/**
 * The template for displaying the content of the listing details widget.
 *
 * This template can be overridden by copying it to yourtheme/posterno/widgets/listing-details.php
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

// Prepare additional fields loop.
$additional_fields = [];

if ( isset( $data->additional_fields ) && ! empty( $data->additional_fields ) ) {

	$fields_to_query = [];

	foreach ( $data->additional_fields as $listing_field ) {
		$fields_to_query[] = $listing_field['field_id'];
	}

	$args = [
		'number'               => 100,
		'listing_meta_key__in' => $fields_to_query,
	];

	$listing_fields = new PNO\Database\Queries\Listing_Fields( $args );

	if ( ! empty( $listing_fields ) && isset( $listing_fields->items ) && is_array( $listing_fields->items ) ) {
		foreach ( $listing_fields->items as $field ) {
			$additional_fields[ $field->getObjectMetaKey() ] = [
				'type' => $field->getType(),
				'name' => $field->getTitle(),
			];
		}
	}

	$additional_fields = pno_sort_array_by_array( $additional_fields, $fields_to_query );

}

?>

<?php if ( ! empty( $additional_fields ) && is_array( $additional_fields ) ) : ?>
	<ul class="list-group mt-4">
		<?php

		foreach ( $additional_fields as $meta_key => $field ) :

			$value = get_post_meta( $listing_id, '_' . $meta_key, true );

			if ( $meta_key === 'listing_social_media_profiles' ) {
				$value = carbon_get_post_meta( $listing_id, 'listing_social_profiles' );
			} elseif ( $meta_key === 'listing_email_address' ) {
				$value = carbon_get_post_meta( $listing_id, 'listing_email' );
			}

			if ( ! $value ) {
				continue;
			}

			?>
			<li class="list-group-item">
				<strong><?php echo esc_html( $field['name'] ); ?></strong>:
				<?php pno_display_field_value( $field['type'], $value, $field ); //phpcs:ignore ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
