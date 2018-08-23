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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$classes            = array( 'input-text' );
$allowed_mime_types = array_keys( ! empty( $data->allowed_mime_types ) ? $data->allowed_mime_types : get_allowed_mime_types() );
$field_name         = isset( $data->name ) ? $data->name : $data->key;
$field_name        .= ! empty( $data->multiple ) ? '[]' : '';
$file_size          = isset( $data->max_size ) ? $data->max_size : false;

$classes[] = 'pno-file-upload';
wp_enqueue_script( 'pno-files-upload' );

?>

<div class="pno-uploaded-files">
	<?php if ( ! empty( $data->value ) ) : ?>
		<?php if ( is_array( $data->value ) ) : ?>
			<?php foreach ( $data->value as $value ) : ?>
				<?php
					posterno()->templates
						->set_template_data(
							[
								'key'   => $data->key,
								'name'  => 'current_' . $field_name,
								'value' => $value,
								'field' => [],
							]
						)
						->get_template_part( 'form-fields/file', 'uploaded' );
				?>
			<?php endforeach; ?>
		<?php elseif ( $value = $data->value ) : ?>
			<?php
				posterno()->templates
					->set_template_data(
						[
							'key'   => $data->key,
							'name'  => 'current_' . $field_name,
							'value' => $value,
							'field' => [],
						]
					)
					->get_template_part( 'form-fields/file', 'uploaded' );
			?>
		<?php endif; ?>
	<?php endif; ?>
</div>

<div class="progress d-none">
	<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
</div>

<input type="file" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-file_types="<?php echo esc_attr( implode( '|', $allowed_mime_types ) ); ?>" <?php if ( ! empty( $data->multiple ) ) echo 'multiple'; ?> name="<?php echo esc_attr( isset( $data->name ) ? $data->name : $data->key ); ?><?php if ( ! empty( $data->multiple ) ) echo '[]'; ?>" id="<?php echo esc_attr( $data->key ); ?>" placeholder="<?php echo empty( $data->placeholder ) ? '' : esc_attr( $data->placeholder ); ?>" />
<small class="description">
	<?php if ( ! empty( $data->description ) ) : ?>
		<?php echo esc_html( $data->description ); ?>
	<?php endif ?>

	<?php printf( esc_html__( 'Maximum file size: %s.' ), esc_html( pno_max_upload_size( '', $file_size ) ) ); ?>
</small>
