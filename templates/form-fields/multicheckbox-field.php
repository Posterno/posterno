<?php
/**
 * The template for displaying the checkboxes field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/checkboxes-field.php
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

?>

<?php foreach ( $data->get_choices() as $opt_key => $value ) : ?>

	<div class="custom-control custom-checkbox">
		<input
			type="checkbox"
			<?php pno_form_field_input_class( $data ); ?>
			name="<?php echo esc_attr( $data->get_name() ); ?>[]"
			<?php if ( ! empty( $data->get_value() ) && is_array( $data->get_value() ) ) checked( in_array( $opt_key, $data->get_value() ), true ); ?>
			value="<?php echo esc_attr( $opt_key ); ?>"
			id="<?php echo esc_attr( $opt_key ); ?>"
		/>
		<label class="custom-control-label" for="<?php echo esc_attr( $opt_key ); ?>">
			<?php echo esc_html( $value ); ?>
		</label>
	</div>

<?php endforeach; ?>
