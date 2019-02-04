<?php
/**
 * The template for displaying the a taxonomy term checklist.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/term-checklist-field.php
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

$queried_taxonomy = $data->get_taxonomy();
$terms    = [];

if ( ! empty( $queried_taxonomy ) ) {

	$args = apply_filters(
		'pno_terms_checklist_settings',
		[
			'taxonomy'   => $queried_taxonomy,
			'hide_empty' => false,
		],
		$data,
		$queried_taxonomy
	);

	$terms = get_terms( $args );

}

?>

<?php if ( ! empty( $terms ) ) : ?>

	<?php foreach ( $terms as $found_term ) : ?>

		<div class="custom-control custom-checkbox">

			<input
				type="checkbox"
				<?php pno_form_field_input_class( $data ); ?>
				name="<?php echo esc_attr( $data->get_object_meta_key() ); ?>[]"
				<?php echo $data->get_attributes(); //phpcs:ignore ?>
				<?php if ( ! empty( $data->get_value() ) && is_array( $data->get_value() ) ) checked( in_array( $found_term->term_id, $data->get_value() ), true ); ?>
				value="<?php echo esc_attr( $found_term->term_id ); ?>"
				id="pno-field-<?php echo esc_attr( $data->get_object_meta_key() ); ?>-<?php echo esc_attr( $found_term->term_id ); ?>"
			/>

			<label for="pno-field-<?php echo esc_attr( $data->get_object_meta_key() ); ?>-<?php echo esc_attr( $found_term->term_id ); ?>" class="custom-control-label">
				<?php echo wp_kses_post( $found_term->name ); ?>
			</label>

		</div>

	<?php endforeach; ?>

<?php endif; ?>


