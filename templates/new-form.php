<?php
/**
 * The template for displaying pno's forms.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form.php
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

?>

<div class="pno-form-container">

	<?php if ( isset( $data->title ) && ! empty( $data->title ) ) : ?>
		<h2><?php echo esc_html( $data->title ); ?></h2>
	<?php endif; ?>

	<?php if ( isset( $data->message ) && ! empty( $data->message ) ) : ?>
		<p><?php echo wp_kses_post( $data->message ); ?></p>
	<?php endif; ?>

	<?php
	if ( ! empty( $data->form->getAllErrors() ) || ! empty( $data->form->getProcessingError() ) ) {

		$error_message = esc_html__( 'There was a problem with your submission. Errors have been highlighted below.' );

		if ( ! empty( $data->form->getProcessingError() ) ) {
			$error_message = $data->form->getProcessingError();
		}

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'danger',
					'message' => wp_kses_post( $error_message ),
				]
			)
			->get_template_part( 'message' );
	}

	if ( ! empty( $data->form->getSuccessMessage() ) ) {

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'success',
					'message' => wp_kses_post( $data->form->getSuccessMessage() ),
				]
			)
			->get_template_part( 'message' );

	}
	?>

	<form action="<?php echo esc_url( $data->form->getAction() ); ?>" method="post" id="pno-form-<?php echo esc_attr( $data->form_name ); ?>" enctype="multipart/form-data">

		<?php foreach ( $data->form->getFields() as $field ) : ?>

			<div <?php pno_form_field_class( $field ); ?>>

				<?php if ( ! empty( $field->getLabel() ) && $field->getType() !== 'checkbox' ) : ?>
					<label for="<?php echo esc_attr( $field->getName() ); ?>"><?php echo esc_html( $field->getLabel() ); ?></label>
				<?php endif; ?>

				<?php if ( ! $field->isRequired() && ! $field->isButton() && $field->getType() !== 'checkbox' ) : ?>
					<span class="pno-optional"><?php esc_html_e( '(optional)', 'posterno' ); ?></span>
				<?php endif; ?>

				<?php echo $field->render(); ?>

				<?php if ( $field->hasErrors() ) : ?>
					<div class="invalid-feedback">
						<?php echo esc_html( $field->getFirstErrorMessage() ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $field->getType() === 'file' && ! empty( $field->getMaxSize() ) ) : ?>
					<small class="form-text text-muted">
						<?php printf( esc_html__( 'Maximum file size: %s.', 'posterno' ), pno_max_upload_size( $field->getMaxSize() ) ); ?>
					</small>
				<?php endif; ?>

				<?php

				// We move the position of the label only for some fields.
				if ( ! empty( $field->getLabel() ) && $field->getType() === 'checkbox' ) :
					?>
					<label for="<?php echo esc_attr( $field->getName() ); ?>" class="custom-control-label"><?php echo wp_kses_post( $field->getLabel() ); ?></label>
				<?php endif; ?>

				<?php if ( ! empty( $field->getHint() ) ) : ?>
					<small class="form-text text-muted">
						<?php echo esc_html( $field->getHint() ); ?>
					</small>
				<?php endif; ?>

			</div>

		<?php endforeach; ?>

		<input type="hidden" name="pno_form" value="<?php echo esc_attr( $data->form_name ); ?>" />
		<?php wp_nonce_field( 'verify_' . esc_attr( $data->form_name ) . '_form', esc_attr( $data->form_name ) . '_nonce' ); ?>

	</form>

</div>
