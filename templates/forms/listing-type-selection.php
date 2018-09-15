<?php
/**
 * The template for displaying the listing type selector part.
 *
 * This template can be overridden by copying it to yourtheme/pno/forms/listing-type-selecotr.php
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

	<?php

	posterno()->templates
		->set_template_data( $data )
		->get_template_part( 'forms/steps' );

	?>

	<div class="row pno-types-container">
		<?php foreach ( pno_get_listings_types() as $type_id => $type_name ) : ?>

			<div class="col-sm-4">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title"><?php echo esc_html( $type_name ); ?></h5>
						<p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
						<form action="<?php echo esc_url( $data->action ); ?>" method="post" id="<?php echo esc_attr( pno_get_form_id( $data->form ) ); ?>-<?php echo esc_attr( $type_id ); ?>" enctype="multipart/form-data">
							<input type="hidden" name="pno_form" value="<?php echo esc_attr( $data->form ); ?>" />
							<input type="hidden" name="step" value="<?php echo esc_attr( $data->step ); ?>" />
							<input type="hidden" name="submit_<?php echo esc_attr( $data->form ); ?>" value="<?php echo esc_attr( $data->form ); ?>">
							<?php wp_nonce_field( 'verify_' . $data->form . '_form', $data->form . '_nonce' ); ?>
							<button type="submit" class="btn btn-secondary"><?php echo esc_html( $data->submit_label ); ?></button>
						</form>
					</div>
				</div>
			</div>

		<?php endforeach; ?>
	</div>

</div>
