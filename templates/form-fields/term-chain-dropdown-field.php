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

// Get selected value.
if ( ! empty( $data->get_value() ) ) {
	$selected = $data->get_value();
} else {
	$selected = '';
}

$terms = pno_get_taxonomy_hierarchy_for_chain_selector( $data->get_taxonomy() );

if ( empty( $terms ) ) {
	return;
}

var_dump( $data->is_multiple() );

?>
<pno-term-chain-select-field inline-template taxonomy="<?php echo esc_attr( $data->get_taxonomy() ); ?>" terms="<?php echo esc_attr( htmlspecialchars( wp_json_encode( $terms ) ) ); ?>">
	<div class="pno-term-chain">
		<treeselect
			v-model="value"
			<?php if ( $data->is_branch_nodes_disabled() === true ) : ?>:disable-branch-nodes="true"<?php endif; ?>
			<?php if ( $data->is_multiple() ) : ?>:multiple="true"<?php endif; ?>
			:options="options"
			value-consists-of="ALL"
			placeholder="<?php echo esc_html( $data->get_placeholder() ); ?>"
		/>
	</div>
</pno-term-chain-select-field>
