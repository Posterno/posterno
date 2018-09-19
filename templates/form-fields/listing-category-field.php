<?php
/**
 * The template for displaying the radio field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/listing-category-field.php
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
if ( ! defined( 'ABSPATH' ) ) exit;

$listing_type_id     = pno_get_submission_queried_listing_type_id();
$listings_categories = pno_get_listings_categories_for_select( $listing_type_id );

$multiple = pno_get_option( 'submission_categories_multiple' ) ? 'multiple' : false;

?>

<select
	class="form-control pno-listings-category-selector"
	name="<?php echo esc_attr( isset( $data->name ) ? $data->name : $data->key ); ?>"
	id="<?php echo esc_attr( $data->key ); ?>"
	<?php if ( ! empty( $data->required ) ) echo 'required'; ?>
	data-placeholder="<?php echo empty( $data->placeholder ) ? '' : esc_attr( $data->placeholder ); ?>"
	<?php echo esc_attr( $multiple ); ?>
	>

	<?php if ( ! empty( $listings_categories ) && is_array( $listings_categories ) && pno_get_option( 'submission_categories_sublevel' ) ) : ?>

		<?php foreach ( $listings_categories as $listing_category ) : ?>
			<optgroup label="<?php echo esc_html( $listing_category['parent_name'] ); ?>">
				<?php foreach ( $listing_category['subcategories'] as $subcategory ) : ?>
					<option value="<?php echo absint( $subcategory['id'] ); ?>"><?php echo esc_html( $subcategory['name'] ); ?></option>
				<?php endforeach; ?>
			</optgroup>
		<?php endforeach; ?>

	<?php else : ?>

		<?php foreach ( $listings_categories as $category_id => $category_name ) : ?>
			<option value="<?php echo absint( $category_id ); ?>"><?php echo esc_html( $category_name ); ?></option>
		<?php endforeach; ?>

	<?php endif; ?>

</select>
<?php if ( ! empty( $data->description ) ) : ?><small class="form-text text-muted"><?php echo $data->description; ?></small><?php endif; ?>
