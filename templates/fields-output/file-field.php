<?php
/**
 * The template for displaying the output of file fields content in profiles or listings pages.
 *
 * This template can be overridden by copying it to yourtheme/posterno/output/file-field.php
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

$files_to_display = isset( $data->files ) ? $data->files : $data->file_url;

?>
<div class="pno-uploaded-file">

	<?php if ( is_array( $files_to_display ) ) : ?>

		<div class="row">

			<?php
			foreach ( $files_to_display as $file ) :

				$file_url = isset( $file['value'] ) ? $file['value'] : false;
				$file_url = is_numeric( $file_url ) ? wp_get_attachment_url( $file_url ) : $file_url;

				$extension = substr( strrchr( $file_url, '.' ), 1 );

				if ( 'image' === wp_ext2type( $extension ) ) :
					?>
					<div class="col-12 col-md-4">
						<img src="<?php echo esc_url( $file_url ); ?>" />
					</div>
				<?php else : ?>
					<div class="col-12">
						<code>
							<?php echo esc_html( basename( $file_url ) ); ?>
						</code>
					</div>
				<?php

				endif;

			endforeach;
			?>

		</div>

	<?php else : ?>

		<?php

		$single_file = is_numeric( $files_to_display ) ? wp_get_attachment_url( $files_to_display ) : $files_to_display;

		$extension = substr( strrchr( $single_file, '.' ), 1 );

		if ( 'image' === wp_ext2type( $extension ) ) :
			?>
			<div class="row">
				<div class="col-12 col-md-4">
					<img src="<?php echo esc_url( $single_file ); ?>" />
				</div>
			</div>
		<?php else : ?>
			<code>
				<?php echo esc_html( basename( $single_file ) ); ?>
			</code>
		<?php endif; ?>

	<?php endif; ?>

</div>
