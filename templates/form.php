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
 * @package posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$class = 'row';

?>

<div class="pno-template pno-form">

	<?php if ( isset( $data->title ) && ! empty( $data->title ) ) : ?>
		<h2><?php echo esc_html( $data->title ); ?></h2>
	<?php endif; ?>

	<?php if ( isset( $data->message ) && ! empty( $data->message ) ) : ?>
		<p><?php echo wp_kses_post( $data->message ); ?></p>
	<?php endif; ?>

	<?php

	/**
	 * Action that fires before the markup of the form actually starts.
	 *
	 * @param string $form the name of the form.
	 */
	do_action( "pno_before_{$data->name}_form", $data->form );

	// Display error or success message if available.
	if ( $message_type && $message ) {
		posterno()->templates
			->set_template_data(
				[
					'type'    => $message_type,
					'message' => $message,
				]
			)
			->get_template_part( 'message' );
	}

	?>

	<form action="<?php echo esc_url( $data->action ); ?>" method="post" id="pno-form-<?php echo esc_attr( strtolower( $data->name ) ); ?>" enctype="multipart/form-data" class="<?php echo esc_attr( $class ); ?>">
heeheh
		<input type="hidden" name="pno_form" value="<?php echo esc_attr( $data->name ); ?>" />
		<input type="hidden" name="submit_<?php echo esc_attr( $data->name ); ?>" value="<?php echo esc_attr( $data->name ); ?>">
		<?php wp_nonce_field( 'verify_' . $data->name . '_form', $data->name . '_nonce' ); ?>

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
	do_action( "pno_after_{$data->name}_form", $data->form );

	?>

</div>
