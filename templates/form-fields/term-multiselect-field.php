<?php
/**
 * The template for a multiselect terms dropdown field.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( isset( $data->value ) ) {
	$selected = $data->value;
} elseif ( isset( $data->default ) && is_int( $data->default ) ) {
	$selected = $data->default;
} elseif ( ! empty( $data->default ) && ( $term = get_term_by( 'slug', $data->default, $data->taxonomy ) ) ) {
	$selected = $term->term_id;
} else {
	$selected = '';
}

$args = array(
	'taxonomy'     => $data->taxonomy,
	'hierarchical' => 1,
	'name'         => isset( $data->name ) ? $data->name : $data->key,
	'orderby'      => 'name',
	'selected'     => $selected,
	'hide_empty'   => false,
);

if ( isset( $data->placeholder ) && ! empty( $data->placeholder ) ) {
	$args['placeholder'] = $data->placeholder;
}

pno_dropdown_categories( apply_filters( 'pno_term_multiselect_field_args', $args ) );

?>

<?php if ( ! empty( $data->description ) ) : ?>
	<small class="form-text text-muted">
		<?php echo wp_kses( $data->description ); ?>
	</small>
<?php endif; ?>
