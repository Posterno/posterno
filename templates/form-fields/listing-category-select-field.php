<?php
/**
 * The template for displaying the listings category select field into the submission form.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/listing-category-select-field.php
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<pno-listings-category-select inline-template>
	<div id="pno-listings-category-selector">
		<div class="input-group mb-3">
			<select class="custom-select" id="inputGroupSelect02">
				<option selected>Choose...</option>
				<option value="1">One</option>
				<option value="2">Two</option>
				<option value="3">Three</option>
			</select>
			<div class="input-group-append">
				<span class="input-group-text" id="inputGroupFileAddon02"><i class="fas fa-spinner fa-spin"></i></span>
			</div>
		</div>
	</div>
</pno-listings-category-select>

<input
	type="hidden"
	name="<?php echo esc_attr( isset( $data->name ) ? $data->name : $data->key ); ?>"
	id="<?php echo esc_attr( $data->key ); ?>"
	value="<?php echo isset( $data->value ) ? esc_attr( $data->value ) : ''; ?>"
>
