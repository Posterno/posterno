<?php
/**
 * The template for displaying general forms.
 *
 * This template can be overridden by copying it to yourtheme/pno/forms/general-form.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

?>

<div class="pno-template pno-form">

	<?php

	/**
	 * Action that fires before the markup of the form actually starts.
	 *
	 * @param string $form the name of the form.
	 */
	do_action( "pno_before_{$data->form}_form", $data->form );

	?>

	<?php if ( isset( $data->message ) && ! empty( $data->message ) ) : ?>
		<p><?php echo esc_html( $data->message ); ?></p>
	<?php endif; ?>

	<form action="<?php echo esc_url( $data->action ); ?>" method="post" id="<?php echo pno_get_form_id( $data->form ); ?>" enctype="multipart/form-data">

		<?php foreach ( $data->fields as $key => $field ) : ?>
			<div <?php pno_form_field_class( $key, $field ); ?>>

				<?php if ( $field['type'] == 'checkbox' ) : ?>

					<?php
						// Add the key to field.
						$field['key'] = $key;
						posterno()->templates
							->set_template_data( $field )
							->get_template_part( 'form-fields/' . $field['type'], 'field' );
					?>

				<?php else : ?>

					<label for="<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $field['label'] ); ?>
						<?php if ( isset( $field['required'] ) && $field['required'] ) : ?>
							<span class="pno-required">*</span>
						<?php endif; ?>
					</label>
					<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
						<?php
							// Add the key to field.
							$field['key'] = $key;
							posterno()->templates
								->set_template_data( $field )
								->get_template_part( 'form-fields/' . $field['type'], 'field' );
						?>
					</div>

				<?php endif; ?>

			</div>
		<?php endforeach; ?>

		<input type="hidden" name="pno_form" value="<?php echo $data->form; ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( $data->step ); ?>" />
		<input type="hidden" name="submit_<?php echo $data->form; ?>" value="<?php echo $data->form; ?>">
		<?php wp_nonce_field( 'verify_' . $data->form . '_form', $data->form . '_nonce' ); ?>
		<button type="submit" class="btn btn-primary"><?php echo esc_html( $data->submit_label ); ?></button>

	</form>

	<?php

	/**
	 * Fires after the markup of the for is finished.
	 *
	 * @param string $form the name of the form.
	 */
	do_action( "pno_after_{$data->form}_form", $data->form );

	?>

</div>
