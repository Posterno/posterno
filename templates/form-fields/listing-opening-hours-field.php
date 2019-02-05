<?php
/**
 * The template for displaying the listing opening hours field.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/opening-hours-field.php
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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$days_of_the_week = pno_get_days_of_the_week();
$time_slots       = pno_get_listing_time_slots();
$active_day       = key( $days_of_the_week );

?>

<pno-listing-opening-hours-selector inline-template>
	<div class="card">
		<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" role="tablist">
				<?php if ( is_array( $days_of_the_week ) && ! empty( $days_of_the_week ) ) : ?>
					<?php foreach ( $days_of_the_week as $day => $day_name ) : ?>
						<li class="nav-item">
							<a class="nav-link <?php if ( $day === $active_day ) : ?>active<?php endif; ?>" href="#timeslot-<?php echo esc_attr( $day ); ?>" id="<?php echo esc_attr( $day ); ?>-tab" data-toggle="tab" aria-controls="timeslot-<?php echo esc_attr( $day ); ?>"><?php echo esc_html( $day_name ); ?></a>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>
		<div class="card-body">

			<div class="tab-content">

				<?php if ( is_array( $days_of_the_week ) && ! empty( $days_of_the_week ) ) : ?>
					<?php foreach ( $days_of_the_week as $day => $day_name ) : ?>

						<div class="tab-pane fade <?php if ( $day === $active_day ) : ?>show active<?php endif; ?>" id="timeslot-<?php echo esc_attr( $day ); ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $day ); ?>-tab">

							<?php if ( is_array( $time_slots ) && ! empty( $time_slots ) ) : ?>
								<?php foreach ( $time_slots as $time_id => $time_name ) : ?>
									<div class="custom-control custom-radio custom-control-inline mb-4">
										<input
											type="radio"
											value="<?php echo esc_attr( $time_id ); ?>"
											v-model="timeslots.<?php echo esc_attr( $day ); ?>.type"
											id="pno_time_slot_<?php echo esc_attr( $time_id ); ?>_<?php echo esc_attr( $day ); ?>"
											name="pno_listing_opening_hours_slot_type_<?php echo esc_attr( $day ); ?>"
											class="custom-control-input"
											@change="maybeResetTimeslots( '<?php echo esc_attr( $day ); ?>' )"
										>
										<label class="custom-control-label" for="pno_time_slot_<?php echo esc_attr( $time_id ); ?>_<?php echo esc_attr( $day ); ?>"><?php echo esc_html( $time_name ); ?></label>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

							<div class="form-row" v-if="canEnterHours( '<?php echo esc_attr( $day ); ?>' )" v-for="(timeslot, index) in timeslots[ '<?php echo esc_attr( $day ); ?>' ].hours" :key="index">

								<div class="col-sm-5">
									<div class="form-group">
										<pno-flatpickr inline-template v-model="timeslots[ '<?php echo esc_attr( $day ); ?>' ].hours[index].opening" :config="{enableTime: true, noCalendar: true, dateFormat: 'H:i'}">
											<input type="text" class="form-control" name="pno_opening_<?php echo esc_attr( $day ); ?>[]" placeholder="<?php esc_html_e( 'Opening time', 'posterno' ); ?>" @input="onInput" >
										</pno-flatpickr>
									</div>
								</div>

								<div class="col-sm-5">
									<div class="form-group">
										<pno-flatpickr inline-template v-model="timeslots[ '<?php echo esc_attr( $day ); ?>' ].hours[index].closing" :config="{enableTime: true, noCalendar: true, dateFormat: 'H:i'}">
											<input type="text" class="form-control" name="pno_closing_<?php echo esc_attr( $day ); ?>[]" placeholder="<?php esc_html_e( 'Closing time', 'posterno' ); ?>" @input="onInput" >
										</pno-flatpickr>
									</div>
								</div>

								<div class="col-sm-2">
									<button type="button" class="btn btn-outline-danger" @click="deleteHours( '<?php echo esc_attr( $day ); ?>', index )" v-if="index > 0">
										<i class="fas fa-trash-alt"></i>
									</button>
								</div>
							</div>

							<button type="button" v-if="canEnterHours( '<?php echo esc_attr( $day ); ?>' )" class="btn btn-secondary btn-sm" @click="addHours( '<?php echo esc_attr( $day ); ?>' )"><?php esc_html_e( 'Add hours', 'posterno' ); ?></button>

						</div>

					<?php endforeach; ?>
				<?php endif; ?>

			</div>

		</div>
	</div>
</pno-listing-opening-hours-selector>

<input
	type="hidden"
	<?php pno_form_field_input_class( $data ); ?>
	name="<?php echo esc_attr( $data->get_object_meta_key() ); ?>"
	id="pno-field-<?php echo esc_attr( $data->get_object_meta_key() ); ?>"
	value="<?php echo ! empty( $data->get_value() ) ? esc_attr( $data->get_value() ) : ''; ?>"
>
