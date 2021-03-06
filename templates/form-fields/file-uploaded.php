<?php
/**
 * The template for displaying the uploaded content for a file field.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/file-uploaded.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.1
 * @package posterno
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$value = is_array( $data->value ) && isset( $data->value['url'] ) ? $data->value['url'] : $data->value;

if ( is_numeric( $value ) ) {
	$image_src = wp_get_attachment_url( absint( $value ) );
} else {
	$image_src = $value;
}

$extension = ! empty( $data->extension ) ? $data->extension : substr( strrchr( $image_src, '.' ), 1 );

?>

<div class="pno-uploaded-file mt-2">
	<?php if ( 'image' === wp_ext2type( $extension ) ) : ?>
		<ul class="list-group pno-uploaded-files-list">
			<li class="list-group-item d-flex justify-content-between align-items-center">
				<img src="<?php echo esc_url( $image_src ); ?>" />
				<a class="pno-remove-uploaded-file btn btn-secondary btn-sm mt-2 mb-2" href="#">
					<?php esc_html_e( 'Remove', 'posterno' ); ?>
				</a>
			</li>
		</ul>
	<?php else : ?>
		<ul class="list-group pno-uploaded-files-list">
			<li class="list-group-item d-flex justify-content-between align-items-center">
				<code>
					<?php echo esc_html( basename( $image_src ) ); ?>
				</code>
				<a class="pno-remove-uploaded-file btn btn-secondary btn-sm" href="#">
					<?php esc_html_e( 'Remove', 'posterno' ); ?>
				</a>
			</li>
		</ul>
	<?php endif; ?>

	<input type="hidden" class="input-text" name="<?php echo esc_attr( $data->name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
</div>
