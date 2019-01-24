<?php
/**
 * Register all widgets powered by Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
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
	register_widget( 'PNO\Widget\ListingLocationMap' );
}
add_action( 'widgets_init', 'pno_register_widgets' );
