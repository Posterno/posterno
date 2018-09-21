<?php
/**
 * The template for displaying the listing tag selection field.
 *
 * This is a Vuejs powered field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/listing-tags-field.php
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

		<div class="col-sm-12" v-if="tagsAreAvailable() && ! loading">
			<div class="pno-checklist-wrapper row">
				<div class="custom-control custom-checkbox col-md-4 col-lg-4" v-for="(option, index) in availableTags" :key="index">
					<input type="checkbox" class="custom-control-input" :id="option.slug" :value="option.term_id" v-model="selectedTags">
					<label class="custom-control-label" :for="option.slug">{{option.name}}</label>
				</div>
			</div>
		</div>
		<p v-else-if="! tagsAreAvailable() && loading">
			<i class="fas fa-spinner fa-spin"></i>
		</p>
		<div class="alert alert-info" role="alert" v-else-if="! tagsAreAvailable() && ! loading">
			<?php esc_html_e( 'Select a category to display related tags.' ); ?>
		</div>

	</div>

</pno-listing-tags-selector>

<input
	type="hidden"
	name="<?php echo esc_attr( isset( $data->name ) ? $data->name : $data->key ); ?>"
	id="<?php echo esc_attr( $data->key ); ?>"
	value="<?php echo isset( $data->value ) ? esc_attr( $data->value ) : ''; ?>"
>

<?php if ( ! empty( $data->description ) ) : ?>
	<small class="form-text text-muted">
		<?php echo wp_kses( $data->description ); ?>
	</small>
<?php endif; ?>
