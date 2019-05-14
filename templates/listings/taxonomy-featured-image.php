<?php
/**
 * The template for displaying the featured image of a taxonomy term.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/taxonomy-featured-image.php
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

$current_term = get_queried_object();
$image        = false;

if ( isset( $current_term->term_id ) ) {
	$image = carbon_get_term_meta( $current_term->term_id, 'term_image' );
}

if ( ! $image ) {
	return;
}

?>

<div class="pno-taxonomy-featured-img media mb-3">
	<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $current_term->name ); ?>">
</div>
