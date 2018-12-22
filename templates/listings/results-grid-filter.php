<?php
/**
 * The template for displaying the results grid filter within the listings loop.
 *
 * This template can be overridden by copying it to yourtheme/pno/listings/results-grid-filter.php
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

$active_layout = pno_get_listings_results_active_layout();

$layouts = pno_get_listings_layout_options();

if ( empty( $layouts ) ) {
	return;
}

?>

<div class="btn-group" role="group" aria-label="<?php esc_html_e( 'Results layout' ); ?>">

	<?php

	/**
	 * Hook: loads before the listings results grid filter.
	 */
	do_action( 'pno_listings_results_before_grid_filter' );

	?>

	<?php foreach ( $layouts as $key => $layout ) : ?>

		<a href="<?php echo esc_url( add_query_arg( [ 'layout' => esc_attr( $key ) ], pno_get_full_page_url() ) ); ?>" class="btn btn-outline-secondary <?php if ( $active_layout === $key ) : ?>active<?php endif; ?>" aria-label="<?php echo esc_html( $layout['label'] ); ?>" data-toggle="tooltip" data-placement="top" title="<?php echo esc_html( $layout['label'] ); ?>">
			<i class="<?php echo esc_attr( $layout['icon'] ); ?>"></i>
		</a>

	<?php endforeach; ?>

	<?php

	/**
	 * Hook: loads after the listings results grid filter.
	 */
	do_action( 'pno_listings_results_after_grid_filter' );

	?>

</div>
