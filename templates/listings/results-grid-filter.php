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

?>

<div class="btn-group" role="group" aria-label="<?php esc_html_e( 'Grid layout' ); ?>">

	<?php
	/**
	 * Hook: loads before the listings results grid filter.
	 */
	do_action( 'pno_listings_results_before_grid_filter' );
	?>

	<button type="button" class="btn btn-outline-secondary" aria-label="<?php esc_html_e( 'List layout' ); ?>">
		<i class="fas fa-list-ul"></i>
	</button>
	<button type="button" class="btn btn-outline-secondary" aria-label="<?php esc_html_e( 'Grid layout' ); ?>">
		<i class="fas fa-th"></i>
	</button>

	<?php
	/**
	 * Hook: loads after the listings results grid filter.
	 */
	do_action( 'pno_listings_results_after_grid_filter' );
	?>

</div>
