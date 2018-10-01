<?php
/**
 * The template for displaying the listing opening hours field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/opening-hours-field.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$days_of_the_week = pno_get_days_of_the_week();
$time_slots = pno_get_listing_time_slots();

?>

<div class="card text-center">
	<div class="card-header">
		<ul class="nav nav-tabs card-header-tabs" role="tablist">
			<?php if ( is_array( $days_of_the_week ) && ! empty( $days_of_the_week ) ) : ?>
				<?php foreach ( $days_of_the_week as $day => $day_name ) : ?>
					<li class="nav-item">
						<a class="nav-link" href="#"><?php echo esc_html( $day_name ); ?></a>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</div>
	<div class="card-body">

		<?php if ( is_array( $time_slots ) && ! empty( $time_slots ) ) : ?>
			<?php foreach ( $time_slots as $time_id => $time_name ) : ?>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pno_time_slot_<?php echo esc_attr( $time_id ); ?>" name="pno_listing_opening_hours_slot_type" class="custom-control-input">
					<label class="custom-control-label" for="pno_time_slot_<?php echo esc_attr( $time_id ); ?>"><?php echo esc_html( $time_name ); ?></label>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>


	</div>
</div>

<input
	type="hidden"
	<?php pno_form_field_input_class( $data ); ?>
	name="<?php echo esc_attr( $data->get_name() ); ?>"
	id="<?php echo esc_attr( $data->get_id() ); ?>"
	value="<?php echo ! empty( $data->get_value() ) ? esc_attr( $data->get_value() ) : ''; ?>"
>
