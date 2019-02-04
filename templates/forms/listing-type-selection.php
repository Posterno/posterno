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

			?>

		<div class="col-sm-4">
			<div class="card">
				<div class="card-body text-center">
					<h5 class="card-title mt-3 mb-3"><?php echo esc_html( $type_name ); ?></h5>
					<form action="<?php echo esc_url( $data->action ); ?>" method="post" id="pno-form-<?php echo esc_attr( strtolower( $data->form->get_form_name() ) ); ?>" enctype="multipart/form-data">
						<input type="hidden" name="listing_type_id" value="<?php echo absint( $type_id ); ?>">
						<input type="hidden" name="pno_form" value="<?php echo esc_attr( $data->form->get_form_name() ); ?>" />
						<input type="hidden" name="step" value="<?php echo esc_attr( $data->step ); ?>" />
						<input type="hidden" name="submit_<?php echo esc_attr( $data->form->get_form_name() ); ?>" value="<?php echo esc_attr( $data->form->get_form_name() ); ?>">
					<?php wp_nonce_field( 'verify_' . $data->form->get_form_name() . '_form', $data->form->get_form_name() . '_nonce' ); ?>
						<button type="submit" class="btn btn-primary mb-3">
						<?php echo esc_html__( 'Select &rarr;' ); ?>
						</button>
					</form>
				</div>
			</div>
		</div>

		<?php endforeach; ?>
	</div>

</div>
