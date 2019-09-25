<?php
/**
 * The template for displaying the a pricing field into the forms.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/pricing-field.php
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

$has_errors = $data->field->hasErrors() ? true : false;

?>

<div class="input-group mb-3">
	<div class="input-group-prepend">
		<span class="input-group-text"><?php echo esc_html( \PNO\CurrencyHelper::get_currency_symbol() ); ?></span>
	</div>
	<input
		type="text"
		class="form-control <?php if ( $has_errors ) : ?>is-invalid<?php endif; ?>"
		name="<?php echo esc_attr( $data->field->getName() ); ?>"
		id="pno-field-<?php echo esc_attr( $data->field->getName() ); ?>"
		placeholder="<?php echo esc_html( $data->field->getAttribute( 'placeholder' ) ); ?>"
		value="<?php echo ! empty( $data->field->getValue() ) ? esc_attr( $data->field->getValue() ) : ''; ?>"
	>
</div>
