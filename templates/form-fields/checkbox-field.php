<?php
/**
 * The template for displaying the checkbox field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/checkbox-field.php
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

?>

<div class="custom-control custom-checkbox">

	<input
		type="checkbox"
		<?php pno_form_field_input_class( $data ); ?>
		name="<?php echo esc_attr( $data->get_object_meta_key() ); ?>"
		id="pno-field-<?php echo esc_attr( $data->get_object_meta_key() ); ?>"
		<?php checked( ! empty( $data->get_value() ), true ); ?>
		<?php echo $data->get_attributes(); //phpcs:ignore ?>
		value="1"
	/>

	<label for="pno-field-<?php echo esc_attr( $data->get_object_meta_key() ); ?>" class="custom-control-label">
		<?php echo wp_kses_post( $data->get_label() ); ?>
	</label>

</div>

