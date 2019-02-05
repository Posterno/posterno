<?php
/**
 * The template for displaying the results order filter listings loop.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/results-grid-filter.php
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

$filters = pno_get_listings_results_order_filters();

if ( ! is_array( $filters ) || empty( $filters ) ) {
	return;
}

$active        = pno_get_listings_results_order_active_filter();
$active_filter = isset( $filters[ $active ] ) ? $filters[ $active ] : $filters[ key( $filters ) ];

?>

<div class="dropdown">
	<button class="btn btn-outline-secondary dropdown-toggle" type="button" id="pno-results-order-filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<?php echo esc_html( sprintf( __( 'Sort by: %s', 'posterno' ), $active_filter['label'] ) ); ?>
	</button>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="pno-results-order-filter">
		<?php foreach ( $filters as $filter_id => $filter ) : ?>
			<a href="<?php echo esc_url( pno_get_listings_results_order_filter_link( $filter_id ) ); ?>" class="dropdown-item <?php if ( $active === $filter_id ) : ?>active<?php endif; ?>"><?php echo esc_html( $filter['label'] ); ?></a>
		<?php endforeach; ?>
	</div>
</div>
