<?php
/**
 * Register all widgets powered by Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register all widgets powered by Posterno.
 *
 * @return void
 */
function pno_register_widgets() {

	$maps_disabled = current_theme_supports( 'posterno_disable_maps' );

	if ( ! $maps_disabled ) {
		register_widget( 'PNO\Widget\ListingLocationMap' );
	}
	register_widget( 'PNO\Widget\ListingVideo' );
	register_widget( 'PNO\Widget\ListingAuthor' );
	register_widget( 'PNO\Widget\ListingContact' );
	register_widget( 'PNO\Widget\ListingDetails' );
	register_widget( 'PNO\Widget\ListingTaxonomies' );
	register_widget( 'PNO\Widget\RecentListings' );
	register_widget( 'PNO\Widget\FeaturedListings' );
	register_widget( 'PNO\Widget\BusinessHours' );
}
add_action( 'widgets_init', 'pno_register_widgets' );
