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

$display_as = pno_get_option( 'submission_tags_display_as' );

?>

<pno-listing-tags-selector inline-template>

	<div>

		<?php if ( $display_as === 'checkboxes' ) : ?>

		<?php else : ?>

			<pno-select2 inline-template v-model="selectedTags" data-placeholder="dd">
				<div class="pno-select2-wrapper">
					<select class="form-control" multiple>

					</select>
				</div>
			</pno-select2>

		<?php endif; ?>

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
