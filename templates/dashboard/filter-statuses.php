<?php
/**
 * The template for displaying the pagination of various listing statuses of Posterno.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/statuses.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 * @package Posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$statuses = pno_get_dashboard_listings_statuses();
$statuses = [ 'all' => esc_html__( 'All listings' ) ] + $statuses;

$active_status       = pno_get_dashboard_active_listings_status();
$active_status_label = $statuses[ $active_status ];

?>
<div class="dropdown pno-dashboard-status-filter">
	<span><?php esc_html_e( 'Status:' ); ?></span>
	<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<?php echo esc_html( $active_status_label ); ?>
	</button>
	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		<?php foreach ( $statuses as $status_key => $status_label ) : ?>
			<a class="dropdown-item <?php if ( $status_key === $active_status ) : ?>active<?php endif; ?>" href="<?php echo esc_url( pno_get_dashboard_listing_status_filter_url( $status_key ) ); ?>"><?php echo esc_html( $status_label ); ?></a>
		<?php endforeach; ?>
	</div>
</div>
