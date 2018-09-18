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
	do_action( "pno_before_{$data->form}_form", $data->form );

	?>

	<?php if ( isset( $data->title ) && ! empty( $data->title ) ) : ?>
		<h2><?php echo $data->title; ?></h2>
	<?php endif; ?>

	<?php if ( isset( $data->message ) && ! empty( $data->message ) ) : ?>
		<p><?php echo $data->message; ?></p>
	<?php endif; ?>

	<form action="<?php echo esc_url( $data->action ); ?>" method="post" id="<?php echo esc_attr( pno_get_form_id( $data->form ) ); ?>" enctype="multipart/form-data" class="row">

		<?php foreach ( $data->fields as $key => $field ) : ?>

			<?php

				/**
				 * Action that triggers before displaying a field within a form.
				 *
				 * @param string $key the id key of the current field.
				 * @param array $field the settings of the current field.
				 * @param string $form the name of the current form.
				 * @param string $step the current step of the form.
				 */
				do_action( 'pno_form_before_field', $key, $field, $data->form, $data->step );

			?>

			<div <?php pno_form_field_class( $key, $field, $data->form ); ?>>
				<?php if ( $field['type'] === 'checkbox' ) : ?>

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
						<?php if ( ! isset( $field['required'] ) || isset( $field['required'] ) && $field['required'] === false ) : ?>
							<small class="pno-optional"><?php esc_html_e( '(optional)' ); ?></small>
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

			<?php

				/**
				 * Action that triggers after displaying a field within a form.
				 *
				 * @param string $key the id key of the current field.
				 * @param array $field the settings of the current field.
				 * @param string $form the name of the current form.
				 * @param string $step the current step of the form.
				 */
				do_action( 'pno_form_after_field', $key, $field, $data->form, $data->step );

			?>

		<?php endforeach; ?>

		<input type="hidden" name="pno_form" value="<?php echo esc_attr( $data->form ); ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( $data->step ); ?>" />
		<input type="hidden" name="submit_<?php echo esc_attr( $data->form ); ?>" value="<?php echo esc_attr( $data->form ); ?>">
		<?php wp_nonce_field( 'verify_' . $data->form . '_form', $data->form . '_nonce' ); ?>

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
	do_action( "pno_after_{$data->form}_form", $data->form );

	?>

</div>
