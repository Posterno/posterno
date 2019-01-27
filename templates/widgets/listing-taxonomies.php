<?php
/**
 * The template for displaying the content of the listing tags widget.
 *
 * This template can be overridden by copying it to yourtheme/pno/widgets/listing-tags.php
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
defined( 'ABSPATH' ) || exit;

$listing_id        = get_queried_object_id();
$selected_taxonomy = isset( $data->taxonomy ) ? $data->taxonomy : false;
$terms             = wp_get_post_terms( $listing_id, $selected_taxonomy );

if ( ! empty( $terms ) && is_array( $terms ) ) {
	foreach ( $terms as $found_term ) :
		?>
			<a href="<?php echo esc_url( get_term_link( $found_term ) ); ?>" class="mr-1 mb-1">
				<span class="badge badge-secondary"><?php echo esc_html( $found_term->name ); ?></span>
			</a>
		<?php
	endforeach;
}
