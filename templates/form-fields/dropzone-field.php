<?php
/**
 * The template for displaying the file upload dropzone field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/dropzone-field.php
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

<input
	type="file"
	<?php pno_form_field_input_class( $data ); ?>
	id="<?php echo esc_attr( $data->get_id() ); ?>"
	aria-describedby="<?php echo esc_attr( $data->get_id() ); ?>"
	<?php if ( $data->get_option( 'multiple' ) ) echo 'multiple'; //phpcs:ignore ?>
	name="<?php echo esc_attr( $data->get_id() ); ?>"
	value=""
	<?php echo $data->get_attributes(); //phpcs:ignore ?>
>
