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

$allowed_mime_types = array_keys( ! empty( $data->get_allowed_mime_types() ) ? $data->get_allowed_mime_types() : get_allowed_mime_types() );
$field_name         = $data->get_object_meta_key();
$field_name        .= $data->is_multiple() ? '[]' : '';
$file_size          = $data->get_maxsize() ? $data->get_maxsize() : false;

?>

<?php if ( ! empty( $data->get_value() ) ) : ?>
	<div class="pno-uploaded-files">

		<?php if ( is_array( $data->get_value() ) ) : ?>

			<?php foreach ( $data->get_value() as $value ) : ?>

				<?php
					posterno()->templates
						->set_template_data(
							[
								'key'   => $data->get_object_meta_key(),
								'name'  => 'current_' . $data->get_object_meta_key(),
								'value' => $value,
							]
						)
						->get_template_part( 'form-fields/file', 'uploaded' );
				?>

			<?php endforeach; ?>

		<?php else : ?>

			<?php
				posterno()->templates
					->set_template_data(
						[
							'key'   => $data->get_object_meta_key(),
							'name'  => 'current_' . $data->get_object_meta_key(),
							'value' => $data->get_value(),
						]
					)
					->get_template_part( 'form-fields/file', 'uploaded' );
			?>

		<?php endif; ?>

	</div>
<?php endif; ?>

<input
	type="file"
	<?php pno_form_field_input_class( $data ); ?>
	name="<?php echo esc_attr( $field_name ); ?>"
	id="pno-field-<?php echo esc_attr( $data->get_object_meta_key() ); ?>"
	aria-describedby="<?php echo esc_attr( $data->get_id() ); ?>"
	<?php if ( $data->is_multiple() ) echo 'multiple'; //phpcs:ignore ?>
	value=""
	<?php echo $data->get_attributes(); //phpcs:ignore ?>
>
