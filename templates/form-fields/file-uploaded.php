<?php
/**
 * The template for displaying the uploaded content for a file field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/file-uploaded.php
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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$value = is_array( $data->value ) ? $data->value['url'] : $data->value;

?>

<div class="pno-uploaded-file">
	<?php
	if ( is_numeric( $value ) ) {
		$image_src = wp_get_attachment_image_src( absint( $value ) );
		$image_src = $image_src ? $image_src[0] : '';
	} else {
		$image_src = $value;
	}
	$extension = ! empty( $data->extension ) ? $data->extension : substr( strrchr( $image_src, '.' ), 1 );
	if ( 'image' === wp_ext2type( $extension ) ) :
	?>
		<span class="pno-uploaded-file-preview"><img src="<?php echo esc_url( $image_src ); ?>" /> <a class="pno-remove-uploaded-file btn btn-secondary btn-sm mt-2 mb-2" href="#" data-dropped="dropzone-<?php echo esc_attr( $data->key ); ?>"><?php esc_html_e( 'Remove' ); ?></a></span>
	<?php else : ?>
		<span class="pno-uploaded-file-name"><code><?php echo esc_html( basename( $image_src ) ); ?></code> <a class="pno-remove-uploaded-file btn btn-secondary btn-sm" href="#" data-dropped="dropzone-<?php echo esc_attr( $data->key ); ?>"><?php esc_html_e( 'Remove' ); ?></a></span>
	<?php endif; ?>

	<input type="hidden" class="input-text" name="<?php echo esc_attr( $data->name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
</div>