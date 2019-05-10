<?php
/**
 * The template for displaying the listing tag selection field.
 *
 * This is a Vuejs powered field.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/listing-tags-field.php
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

?>

<pno-listing-tags-selector inline-template>

	<div class="tags-selector-wrapper">

		<pno-select2 inline-template v-if="tagsAreAvailable() && ! loading" v-model="selectedTags" :options="availableTags" data-placeholder="<?php echo esc_html( $data->getAttribute( 'placeholder' ) ); ?>" data-emitterid="category-tags">
			<div class="pno-select2-wrapper">
				<select class="form-control" multiple>
				</select>
			</div>
		</pno-select2>

		<div class="pno-loading" v-else-if="! tagsAreAvailable() && loading"></div>

		<div class="alert alert-info" role="alert" v-else-if="! tagsAreAvailable() && ! loading">
			<?php esc_html_e( 'Select a category to display related tags.', 'posterno' ); ?>
		</div>

	</div>

</pno-listing-tags-selector>

<input
	type="hidden"
	name="<?php echo esc_attr( $data->field->getName() ); ?>"
	id="pno-field-<?php echo esc_attr( $data->field->getName() ); ?>"
	value="<?php echo ! empty( $data->field->getValue() ) ? esc_attr( $data->getValue() ) : ''; ?>"
>

