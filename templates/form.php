<?php
/**
 * The template for displaying pno's forms.
 *
 * This template can be overridden by copying it to yourtheme/pno/form.php
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

<div class="pno-template pno-form">

	<?php

	/**
	 * Action that fires before the markup of the form actually starts.
	 *
	 * @param string $form the name of the form.
	 */
	do_action( "pno_before_{$data->form->get_name()}_form", $data->form );

	?>

	<form action="<?php echo esc_url( home_url() ); ?>" method="post" id="pno-form-<?php echo esc_attr( strtolower( $data->form->get_name() ) ); ?>" enctype="multipart/form-data" class="row">

		<?php $data->form->render(); ?>

		<input type="hidden" name="pno_form" value="<?php echo esc_attr( $data->form->get_name() ); ?>" />
		<input type="hidden" name="submit_<?php echo esc_attr( $data->form->get_name() ); ?>" value="<?php echo esc_attr( $data->form->get_name() ); ?>">
		<?php wp_nonce_field( 'verify_' . $data->form->get_name() . '_form', $data->form->get_name() . '_nonce' ); ?>

		<div class="col-sm-12">
			<button type="submit" class="btn btn-primary">
				<?php echo esc_html( $data->submit_label ); ?>
			</button>
		</div>

	</form>

	<?php

	/**
	 * Fires after the markup of the for is finished.
	 *
	 * @param string $form the name of the form.
	 */
	do_action( "pno_after_{$data->form->get_name()}_form", $data->form );

	?>

</div>
