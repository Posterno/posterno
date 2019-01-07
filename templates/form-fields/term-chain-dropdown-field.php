<?php
/**
 * The template for displaying the a taxonomy term chain selection dropdown.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/term-chain-dropdown-field.php
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

$terms = pno_get_taxonomy_hierarchy_for_chain_selector( $data->get_taxonomy() );

if ( empty( $terms ) ) {
	return;
}

// If the field has been marked as non multple but the value is an array, we somehow
// need to get a single value only, this is ugly and not a perfect way, but for now this will do it.
$value = $data->get_value();

if ( ! $data->is_multiple() ) {
	$decoded = json_decode( $value );
	if ( is_array( $decoded ) ) {
		$args         = [
			'include'  => $decoded,
			'taxonomy' => $data->get_taxonomy(),
		];
		$unique_terms = get_terms( $args );
		if ( ! empty( $unique_terms ) && is_array( $unique_terms ) ) {
			$unique_term = wp_filter_object_list( $unique_terms, [ 'parent' => 0 ], 'not' );
			$value       = isset( $unique_term[ key( $unique_term ) ]->term_id ) ? absint( $unique_term[ key( $unique_term ) ]->term_id ) : $value;
		}
	}
}

?>
<pno-term-chain-select-field inline-template taxonomy="<?php echo esc_attr( $data->get_taxonomy() ); ?>" terms="<?php echo esc_attr( htmlspecialchars( wp_json_encode( $terms ) ) ); ?>">
	<div>
		<treeselect
			v-model="value"
			<?php if ( $data->is_branch_nodes_disabled() === true ) : ?>
			:disable-branch-nodes="true"
			<?php endif; ?>
			<?php if ( $data->is_multiple() ) : ?>
			:multiple="true"
			<?php endif; ?>
			:options="options"
			value-consists-of="ALL"
			no-results-text="<?php esc_html_e( 'No results found' ); ?>"
			no-options-text="<?php esc_html_e( 'No options available.' ); ?>"
			placeholder="<?php echo esc_html( $data->get_placeholder() ); ?>"
		/>
	</div>
</pno-term-chain-select-field>

<input
	type="hidden"
	name="<?php echo esc_attr( $data->get_object_meta_key() ); ?>"
	id="pno-field-<?php echo esc_attr( $data->get_object_meta_key() ); ?>"
	class="pno-chain-select-value-holder" <?php // Do not change. ?>
	value="<?php echo ! empty( $value ) ? esc_attr( $value ) : ''; ?>"
>
