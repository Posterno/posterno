<?php
/**
 * The template for displaying the output of taxonomy fields content in profiles or listings pages.
 *
 * This template can be overridden by copying it to yourtheme/posterno/output/file-field.php
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
defined( 'ABSPATH' ) || exit;

$terms_to_display = [];

foreach ( $data->terms as $term ) {
	$terms_to_display[] = esc_html( $term->name );
}

if ( ! empty( $terms_to_display ) ) {
	echo implode( ', ', $terms_to_display ); //phpcs:ignore
}
