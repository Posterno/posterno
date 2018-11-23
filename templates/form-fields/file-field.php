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
$field_name         = $data->get_name();
$field_name        .= ! empty( $data->is_multiple() ) ? '[]' : '';
$file_size          = $data->get_maxsize() ? $data->get_maxsize() : false;

// Determine the type of form we're working with.
$form_type = 'user_meta';

if ( $form_type === 'user_meta' ) {
	$field_id = $data->get_id() === 'avatar' ? 'current_user_avatar' : $data->get_id();
	$value    = carbon_get_user_meta( get_current_user_id(), $field_id );
} else {
	$value = carbon_get_post_meta( get_current_user_id(), $data->get_id() );
}

$stored_value = $value;

if ( is_array( $stored_value ) && isset( $stored_value['url'] ) ) {
	$stored_value = $stored_value['url'];
}

?>

<?php if ( ! empty( $stored_value ) ) : ?>
	<?php if ( is_array( $stored_value ) && ! isset( $stored_value ) ) : ?>
		<?php foreach ( $stored_value as $value ) : ?>
			<?php
				posterno()->templates
					->set_template_data(
						[
							'key'   => $data->get_id(),
							'name'  => 'current_' . $data->get_id(),
							'value' => $value,
						]
					)
					->get_template_part( 'form-fields/file', 'uploaded' );
			?>
		<?php endforeach; ?>
	<?php elseif ( $stored_value ) : ?>
			<?php
				posterno()->templates
					->set_template_data(
						[
							'key'   => $data->get_id(),
							'name'  => 'current_' . $data->get_id(),
							'value' => $stored_value,
						]
					)
					->get_template_part( 'form-fields/file', 'uploaded' );
			?>
	<?php endif; ?>
<?php endif; ?>

<input
	type="file"
	<?php pno_form_field_input_class( $data ); ?>
	id="<?php echo esc_attr( $data->get_id() ); ?>"
	aria-describedby="<?php echo esc_attr( $data->get_id() ); ?>"
	<?php if ( $data->is_multiple() ) echo 'multiple'; //phpcs:ignore ?>
	name="<?php echo esc_attr( $data->get_id() ); ?>"
	value=""
	<?php echo $data->get_attributes(); //phpcs:ignore ?>
>
