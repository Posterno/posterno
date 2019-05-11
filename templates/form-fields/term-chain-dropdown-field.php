<?php
/**
 * The template for displaying the a taxonomy term chain selection dropdown.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/term-chain-dropdown-field.php
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

$terms = pno_get_taxonomy_hierarchy_for_chain_selector( $data->field->getTaxonomy() );

if ( empty( $terms ) ) {
	return;
}

// If the field has been marked as non multple but the value is an array, we somehow
// need to get a single value only, this is ugly and not a perfect way, but for now this will do it.
$value = $data->field->getValue();

if ( ! $data->field->isMultiple() ) {
	$decoded = json_decode( $value );
	if ( is_array( $decoded ) ) {
		$args         = [
			'include'  => $decoded,
			'taxonomy' => $data->field->getTaxonomy(),
		];
		$unique_terms = get_terms( $args );
		if ( ! empty( $unique_terms ) && is_array( $unique_terms ) ) {
			$unique_term = wp_filter_object_list( $unique_terms, [ 'parent' => 0 ], 'not' );
			$value       = isset( $unique_term[ key( $unique_term ) ]->term_id ) ? absint( $unique_term[ key( $unique_term ) ]->term_id ) : $value;
		}
	}
}

?>
<pno-term-chain-select-field inline-template taxonomy="<?php echo esc_attr( $data->field->getTaxonomy() ); ?>" terms="<?php echo esc_attr( htmlspecialchars( wp_json_encode( $terms ) ) ); ?>">
	<div>
		<treeselect
			v-model="value"
			<?php if ( $data->field->isBranchDisabled() === true ) : ?>
			:disable-branch-nodes="true"
			<?php endif; ?>
			<?php if ( $data->field->isMultiple() ) : ?>
			:multiple="true"
			<?php endif; ?>
			:options="options"
			value-consists-of="ALL"
			no-results-text="<?php esc_html_e( 'No results found', 'posterno' ); ?>"
			no-options-text="<?php esc_html_e( 'No options available.', 'posterno' ); ?>"
			placeholder="<?php echo esc_html( $data->field->getAttribute( 'placeholder' ) ); ?>"
		/>
	</div>
</pno-term-chain-select-field>

<input
	type="hidden"
	name="<?php echo esc_attr( $data->field->getName() ); ?>"
	id="pno-field-<?php echo esc_attr( $data->field->getName() ); ?>"
	<?php if ( $has_errors ) : ?>
	class="form-control is-invalid"
	<?php endif; ?>
	value="<?php echo ! empty( $data->field->getValue() ) ? esc_attr( $data->field->getValue() ) : ''; ?>"
>
