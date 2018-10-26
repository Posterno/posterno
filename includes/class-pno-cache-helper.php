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
	 * Initialize cache hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'flush_user_has_submitted_listings' ) );
		add_action( 'delete_post', array( __CLASS__, 'flush_user_has_submitted_listings' ) );
		add_action( 'trash_post', array( __CLASS__, 'flush_user_has_submitted_listings' ) );

		add_action( 'save_post', array( __CLASS__, 'flush_fields_cache' ) );
		add_action( 'delete_post', array( __CLASS__, 'flush_fields_cache' ) );
		add_action( 'trash_post', array( __CLASS__, 'flush_fields_cache' ) );
	}

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
	 * @param string $version what we're going to delete.
	 */
	private static function delete_version_transients( $version ) {
		if ( ! wp_using_ext_object_cache() && ! empty( $version ) ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s;", '\_transient\_%' . $version ) );
		}
	}

	/**
	 * Flush the cache generated when checking if a user has submitted listings.
	 *
	 * @param string|int $listing_id the listing id being updated.
	 * @return void
	 */
	public static function flush_user_has_submitted_listings( $listing_id ) {
		if ( 'listings' === get_post_type( $listing_id ) ) {
			$user_id = pno_get_listing_author( $listing_id );
			wp_cache_forget( "user_has_submitted_listings_{$user_id}" );
		}
	}

	/**
	 * Flush the cache generated for the fields.
	 *
	 * @param string|int $post_id the id of the post being updated.
	 * @return void
	 */
	public static function flush_fields_cache( $post_id ) {
		if ( 'pno_signup_fields' === get_post_type( $post_id ) ) {
			forget_transient( 'pno_registration_fields' );
		}
	}

}

Helper::init();
