<?php
/**
 * The template for displaying the a taxonomy term selection dropdown.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/term-select-field.php
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
} elseif ( is_int( $data->get_option( 'default' ) ) ) {
	$selected = $data->get_option( 'default' );
} elseif ( ! empty( $data->get_option( 'default' ) ) && ( $term = get_term_by( 'slug', $data->get_option( 'default' ), $data->get_option( 'taxonomy' ) ) ) ) {
	$selected = $term->term_id;
} else {
	$selected = '';
}

// Select only supports 1 value.
if ( is_array( $selected ) ) {
	$selected = current( $selected );
}

wp_dropdown_categories(
	apply_filters(
		'pno_term_select_field_wp_dropdown_categories_args', array(
			'taxonomy'         => $data->get_option( 'taxonomy' ),
			'hierarchical'     => 1,
			'show_option_all'  => false,
			'show_option_none' => $data->get_option( 'required' ) ? '' : '-',
			'name'             => esc_attr( $data->get_name() ),
			'orderby'          => 'name',
			'selected'         => $selected,
			'hide_empty'       => false,
			'class'            => join( ' ', pno_get_form_field_input_class( $data ) ),
		), $data
	)
);
