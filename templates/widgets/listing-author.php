<?php
/**
 * The template for displaying the content of the listing author widget.
 *
 * This template can be overridden by copying it to yourtheme/pno/widgets/listing-author.php
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

$display_since = isset( $data->display_member_since ) && $data->display_member_since === true ? true : false;

$listing = get_queried_object();

$listing_id                 = isset( $listing->ID ) ? absint( $listing->ID ) : false;
$listing_author_id          = isset( $listing->post_author ) ? absint( $listing->post_author ) : false;
$listing_author_profile_url = pno_get_member_profile_url( $listing_author_id );

// Prepare additional fields loop.
$additional_fields = [];

if ( isset( $data->additional_fields ) && ! empty( $data->additional_fields ) ) {

	$fields_to_query = [];

	foreach ( $data->additional_fields as $profile_field ) {
		$fields_to_query[] = $profile_field['field_id'];
	}

	$args = [
		'number'            => 100,
		'user_meta_key__in' => $fields_to_query,
	];

	$profile_fields = new PNO\Database\Queries\Profile_Fields( $args );

	if ( ! empty( $profile_fields ) && isset( $profile_fields->items ) && is_array( $profile_fields->items ) ) {
		foreach ( $profile_fields->items as $field ) {
			$additional_fields[ $field->get_object_meta_key() ] = [
				'type' => $field->get_type(),
				'name' => $field->get_name(),
			];
		}
	}
}

?>

<div class="row no-gutters">
	<div class="col-md-4">
		<a href="<?php echo esc_url( $listing_author_profile_url ); ?>">
			<?php echo get_avatar( $listing_author_id, 120 ); ?>
		</a>
	</div>
	<div class="col-md-8 pl-3 align-self-center">
		<h4 class="mb-1">
			<a href="<?php echo esc_url( $listing_author_profile_url ); ?>">
				<?php echo esc_html( pno_get_user_fullname( $listing_author_id ) ); ?>
			</a>
		</h4>
		<?php if ( $display_since ) : ?>
			<?php echo esc_html( sprintf( __( 'Member since: %s' ), pno_get_user_registration_date( $listing_author_id ) ) ); ?>
		<?php endif; ?>
	</div>
</div>

<?php if ( ! empty( $additional_fields ) && is_array( $additional_fields ) ) : ?>
	<ul class="list-group mt-4">
		<?php

		foreach ( $additional_fields as $meta_key => $field ) :

			$value = pno_get_profile_field_value( $listing_author_id, $meta_key );

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
