<?php
/**
 * The template for displaying the radio field.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/radio-field.php
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

$default = ! empty( $data->get_value() ) ? $data->get_value() : '';

foreach ( $data->get_options() as $option_key => $value ) : ?>

	<div class="custom-control custom-radio">
		<input type="radio" class="custom-control-input" id="<?php echo esc_attr( $option_key ); ?>" name="<?php echo esc_attr( $data->get_object_meta_key() ); ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( $default, $option_key ); ?> <?php echo $data->get_attributes(); //phpcs:ignore ?>/>
		<label class="custom-control-label" for="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $value ); ?></label>
	</div>

<?php endforeach; ?>
