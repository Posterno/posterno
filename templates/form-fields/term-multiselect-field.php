<?php
/**
 * The template for displaying the a taxonomy term selection dropdown.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/term-multiselect-field.php
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

// Get selected value.
if ( ! empty( $data->get_value() ) ) {
	$selected = $data->get_value();
} else {
	$selected = '';
}

$args = array(
	'taxonomy'     => $data->get_taxonomy(),
	'hierarchical' => 1,
	'name'         => esc_attr( $data->get_object_meta_key() ),
	'orderby'      => 'name',
	'selected'     => $selected,
	'hide_empty'   => false,
	'class'        => join( ' ', pno_get_form_field_input_class( $data ) ),
);

if ( ! empty( $data->get_placeholder() ) ) {
	$args['placeholder'] = $data->get_placeholder();
}

pno_dropdown_categories( apply_filters( 'pno_term_multiselect_field_args', $args ) );
