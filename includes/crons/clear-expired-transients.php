<?php
/**
 * Cron job: clear expired transients belonging to Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Cron;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Clear transients generated by Posterno.
 *
 * @return void
 */
function clear_expired_transients() {

	global $wpdb;

	if ( ! wp_using_ext_object_cache() && ! defined( 'WP_SETUP_CONFIG' ) && ! defined( 'WP_INSTALLING' ) ) {
		$wpdb->query( $wpdb->prepare( "
			DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %s;",
			$wpdb->esc_like( '_transient_pno_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_pno_' ) . '%',
			time()
		) );
	}

}
add_action( 'posterno_clear_expired_transients', __NAMESPACE__ . '\\clear_expired_transients', 10 );
