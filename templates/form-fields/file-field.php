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

$stored_value = $data->get_value();

if ( is_array( $stored_value ) && isset( $stored_value['url'] ) ) {
	$stored_value = $stored_value['url'];
}

?>

<?php if ( ! empty( $data->get_value() ) ) : ?>
	<?php if ( is_array( $data->get_value() ) && ! isset( $data->get_value()['url'] ) ) : ?>
		<?php foreach ( $data->get_value() as $value ) : ?>
			<?php
				posterno()->templates
					->set_template_data(
						[
							'key'   => $data->get_id(),
							'name'  => 'current_' . $field_name,
							'value' => $value,
							'field' => [],
						]
					)
					->get_template_part( 'form-fields/file', 'uploaded' );
			?>
		<?php endforeach; ?>
	<?php elseif ( $value = $data->get_value() ) : ?>
			<?php
				posterno()->templates
					->set_template_data(
						[
							'key'   => $data->get_id(),
							'name'  => 'current_' . $field_name,
							'value' => $value,
							'field' => [],
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
	<?php if ( $data->get_option( 'multiple' ) ) echo 'multiple'; //phpcs:ignore ?>
	name="<?php echo esc_attr( $data->get_id() ); ?>"
	value="<?php echo esc_attr( $stored_value ) ; ?>"
	<?php echo $data->get_attributes(); //phpcs:ignore ?>
>
