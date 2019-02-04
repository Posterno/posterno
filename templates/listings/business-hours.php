<?php
/**
 * The template for displaying the business hours of a listing.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/business-hours.php
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

$listing_id     = isset( $data->listing_id ) ? absint( $data->listing_id ) : get_queried_object_id();
$business_hours = new \PNO\Listing\BusinessHours( $listing_id );

if ( ! $business_hours->has_business_hours() ) {

	posterno()->templates
		->set_template_data(
			[
				'type'    => 'info',
				'message' => esc_html__( 'This listing does not have business hours set.' ),
			]
		)
		->get_template_part( 'message' );

	return;

}

$opening_hours = $business_hours->get_opening_hours();

?>

<table class="table table-striped pno-business-hours">
	<tbody>
		<?php

		foreach ( $opening_hours as $set ) :

			if ( ! $set->to_string() ) {
				continue;
			}

			?>
			<tr
			<?php if ( $set->is_today() ) : ?>
			class="today"
			<?php endif; ?>
			>
				<td><strong><?php echo esc_html( $set->get_day_name() ); ?></strong></td>
				<td><?php echo wp_kses_post( $set->to_string() ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
