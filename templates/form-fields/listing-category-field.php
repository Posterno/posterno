<?php
/**
 * The template for displaying the category selection field.
 *
 * This is a Vuejs powered field.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/listing-category-field.php
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

// Retrieve categories.
$listing_type_id                = pno_get_submission_queried_listing_type_id();
$listings_categories_associated = pno_get_listings_categories_for_submission_selection( $listing_type_id );

?>

<pno-listing-category-selector inline-template emitterid="categories-changed" terms="<?php echo esc_attr( htmlspecialchars( wp_json_encode( $listings_categories_associated ) ) ); ?>">
	<div>
		<treeselect
			v-model="value"
			<?php if ( pno_get_option( 'submission_categories_disable_nodes' ) ) : ?>:disable-branch-nodes="true"<?php endif; ?>
			<?php if ( pno_get_option( 'submission_categories_multiple' ) ) : ?>:multiple="true"<?php endif; ?>
			:options="options"
			value-consists-of="ALL"
			no-results-text="<?php esc_html_e( 'No results found', 'posterno' ); ?>"
			no-options-text="<?php esc_html_e( 'No options available.', 'posterno' ); ?>"
			placeholder="<?php echo esc_html( $data->field->getAttribute( 'placeholder' ) ); ?>"
		/>
	</div>
</pno-listing-category-selector>

<input
	type="hidden"
	name="<?php echo esc_attr( $data->field->getName() ); ?>"
	id="pno-field-<?php echo esc_attr( $data->field->getName() ); ?>"
	value="<?php echo ! empty( $data->field->getValue() ) ? esc_attr( $data->getValue() ) : ''; ?>"
>

