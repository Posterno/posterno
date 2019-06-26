<?php
/**
 * The template for displaying the listing type selector part.
 *
 * This template can be overridden by copying it to yourtheme/posterno/forms/listing-type-selection.php
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
defined( 'ABSPATH' ) || exit;

?>

<div id="pno-listing-type-selection">

	<div class="row pno-types-container">
		<?php

		foreach ( pno_get_listings_types() as $type_id => $type_name ) :

			$submission_url = add_query_arg(
				[
					'listing_type' => absint( $type_id ),
				],
				get_permalink()
			);

			$icon = carbon_get_term_meta( $type_id, 'term_icon' );

			?>

		<div class="col-sm-4">
			<div class="card">
				<div class="card-body text-center">
					<?php if ( $icon ) : ?>
						<div class="term-icon rounded-circle">
							<i class="<?php echo esc_attr( $icon ); ?>"></i>
						</div>
					<?php endif; ?>
					<h5 class="card-title mt-3 mb-3"><?php echo esc_html( $type_name ); ?></h5>
					<form action="<?php echo esc_url( $data->action ); ?>" method="get" enctype="multipart/form-data">
						<input type="hidden" name="listing_type_id" value="<?php echo absint( $type_id ); ?>">
						<input type="hidden" name="submission_step" value="<?php echo esc_attr( $data->step ); ?>" />
						<?php pno_do_listing_form_submission_step_keys(); ?>
						<button type="submit" class="btn btn-primary mb-3">
							<?php echo esc_html__( 'Select &rarr;', 'posterno' ); ?>
						</button>
					</form>
				</div>
			</div>
		</div>

		<?php endforeach; ?>
	</div>

</div>
