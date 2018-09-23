<?php
/**
 * The template for displaying the textarea field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/textarea-field.php
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

<textarea
	<?php pno_form_field_input_class( $data ); ?>
	name="<?php echo esc_attr( $data->get_name() ); ?>"
	id="<?php echo esc_attr( $data->get_id() ); ?>"
	><?php echo ! empty( $data->get_value() ) ? esc_textarea( html_entity_decode( $data->get_value() ) ) : ''; ?>
</textarea>
