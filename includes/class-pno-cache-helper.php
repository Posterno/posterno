<?php
/**
 * Handles cache related functionalities such as storing, purging, updating.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Cache;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Helper class that handles caching related functionalities of Posterno.
 */
class Helper {

	/**
	 * Gets transient version.
	 *
	 * When using transients with unpredictable names, e.g. those containing an md5
	 * hash in the name, we need a way to invalidate them all at once.
	 *
	 * When using default WP transients we're able to do this with a DB query to
	 * delete transients manually.
	 *
	 * With external cache however, this isn't possible. Instead, this function is used
	 * to append a unique string (based on time()) to each transient. When transients
	 * are invalidated, the transient version will increment and data will be regenerated.
	 *
	 * @param  string  $group   Name for the group of transients we need to invalidate.
	 * @param  boolean $refresh True to force a new version (Default: false).
	 * @return string Transient version based on time(), 10 digits.
	 */
	public static function get_transient_version( $group, $refresh = false ) {
		$transient_name  = $group . '-transient-version';
		$transient_value = get_transient( $transient_name );
		if ( false === $transient_value || true === $refresh ) {
			self::delete_version_transients( $transient_value );
			set_transient( $transient_name, $transient_value = time() );
		}
		return $transient_value;
	}

	/**
	 * When the transient version increases, this is used to remove all past transients to avoid filling the DB.
	 *
	 * Note; this only works on transients appended with the transient version, and when object caching is not being used.
	 *
	 * @param string $version
	 */
	private static function delete_version_transients( $version ) {
		if ( ! wp_using_ext_object_cache() && ! empty( $version ) ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s;", '\_transient\_%' . $version ) );
		}
	}

}
