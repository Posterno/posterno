<?php
/**
 * The template for displaying the results listings per page modifier within the listings loop.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/results-listings-per-page.php
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

$results_per_page_options = pno_get_listings_results_per_page_options();
$current_option           = pno_get_listings_results_per_page_active_option();

?>

<div class="dropdown">
	<a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="pno-listings-per-page-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<?php echo sprintf( esc_html__( 'Show %s results' ), absint( $current_option ) ); ?>
	</a>
	<div class="dropdown-menu" aria-labelledby="pno-listings-per-page-menu">
		<?php foreach ( $results_per_page_options as $option ) : ?>
			<a class="dropdown-item <?php if ( absint( $current_option ) === absint( $option ) ) : ?>active<?php endif; ?>" href="<?php echo esc_url( pno_get_listings_results_per_page_option_link( $option ) ); ?>"><?php echo sprintf( esc_html__( 'Show %s' ), absint( $option ) ); ?></a>
		<?php endforeach; ?>
	</div>
</div>
