<?php
/**
 * The template for displaying the file field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/file-field.php
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

$allowed_mime_types = array_keys( ! empty( $data->get_option( 'allowed_mime_types' ) ) ? $data->get_option( 'allowed_mime_types' ) : get_allowed_mime_types() );
$field_name         = $data->get_name();
$field_name        .= ! empty( $data->get_option( 'multiple' ) ) ? '[]' : '';
$file_size          = $data->get_option( 'max_size' ) ? $data->get_option( 'max_size' ) : false;

wp_enqueue_script( 'pno-files-upload' );

?>

<div class="input-group">
	<div class="input-group-prepend">
		<span class="input-group-text" id="<?php echo esc_attr( $data->get_id() ); ?>"><?php esc_html_e( 'Upload' ); ?></span>
	</div>
	<div class="custom-file">
		<input
			type="file"
			<?php pno_form_field_input_class( $data ); ?>
			id="<?php echo esc_attr( $data->get_id() ); ?>"
			aria-describedby="<?php echo esc_attr( $data->get_id() ); ?>"
			<?php if ( $data->get_option( 'multiple' ) ) echo 'multiple'; //phpcs:ignore ?>
			name="<?php echo esc_attr( $data->get_name() ); ?>"
			value="<?php echo ! empty( $data->get_value() ) ? esc_attr( $data->get_value() ) : ''; ?>"
			<?php echo $data->get_attributes(); //phpcs:ignore ?>
		>
		<label class="custom-file-label" for="<?php echo esc_attr( $data->get_id() ); ?>"><?php esc_html_e( 'Choose file' ); ?></label>
	</div>
</div>
