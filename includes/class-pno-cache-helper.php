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

		add_action( 'set_object_terms', array( __CLASS__, 'set_term' ), 10, 4 );
		add_action( 'edited_term', array( __CLASS__, 'edited_term' ), 10, 3 );
		add_action( 'create_term', array( __CLASS__, 'edited_term' ), 10, 3 );
		add_action( 'delete_term', array( __CLASS__, 'edited_term' ), 10, 3 );

		add_action( 'transition_post_status', array( __CLASS__, 'maybe_clear_count_transients' ), 10, 3 );

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
	 * Flush the cache generated for the fields when updating fields in the database.
	 *
	 * @param string|int $post_id the id of the post being updated.
	 * @return void
	 */
	public static function flush_fields_cache( $post_id ) {
		if ( 'pno_signup_fields' === get_post_type( $post_id ) ) {
			forget_transient( 'pno_registration_fields' );
		} elseif ( 'pno_users_fields' === get_post_type( $post_id ) ) {
			forget_transient( 'pno_admin_custom_profile_fields' );
			forget_transient( 'pno_profile_fields_list_for_widget_association' );
		} elseif ( 'pno_listings_fields' === get_post_type( $post_id ) ) {
			forget_transient( 'pno_admin_custom_listing_fields' );
			forget_transient( 'pno_listings_fields_list_for_widget_association' );
			forget_transient( 'pno_get_listings_fields' );
		}
	}

	/**
	 * Flush the cache generated for all form fields.
	 *
	 * @return void
	 */
	public static function flush_all_fields_cache() {
		forget_transient( 'pno_registration_fields' );
		forget_transient( 'pno_admin_custom_profile_fields' );
		forget_transient( 'pno_admin_custom_listing_fields' );
		forget_transient( 'pno_profile_fields_list_for_widget_association' );
		forget_transient( 'pno_listings_fields_list_for_widget_association' );
		forget_transient( 'pno_get_listings_fields' );
	}

	/**
	 * Refreshes the terms cache when terms are updated.
	 *
	 * @param string|int $object_id the object sent through the hook.
	 * @param string     $terms list of terms.
	 * @param string     $tt_ids terms ids.
	 * @param string     $taxonomy taxonomy id.
	 */
	public static function set_term( $object_id = '', $terms = '', $tt_ids = '', $taxonomy = '' ) {
		self::get_transient_version( 'pno_get_' . sanitize_text_field( $taxonomy ), true );
	}
	/**
	 * Refreshes the terms cache when terms are updated.
	 *
	 * @param string|int $term_id term updated.
	 * @param string|int $tt_id id of the term updated.
	 * @param string     $taxonomy taxonomy name updated.
	 */
	public static function edited_term( $term_id = '', $tt_id = '', $taxonomy = '' ) {
		self::get_transient_version( 'pno_get_' . sanitize_text_field( $taxonomy ), true );
	}

	/**
	 * Maybe remove pending count transients
	 *
	 * When a supported post type status is updated, check if any cached count transients
	 * need to be removed, and remove the
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public static function maybe_clear_count_transients( $new_status, $old_status, $post ) {

		global $wpdb;

		/**
		 * Get supported post types for count caching
		 *
		 * @param array   $post_types Post types that should be cached.
		 * @param string  $new_status New post status.
		 * @param string  $old_status Old post status.
		 * @param WP_Post $post       Post object.
		 */
		$post_types = apply_filters( 'pno_count_cache_supported_post_types', array( 'listings' ), $new_status, $old_status, $post );

		// Only proceed when statuses do not match, and post type is supported post type.
		if ( $new_status === $old_status || ! in_array( $post->post_type, $post_types, true ) ) {
			return;
		}

		/**
		 * Get supported post statuses for count caching
		 *
		 * @param array   $post_statuses Post statuses that should be cached.
		 * @param string  $new_status    New post status.
		 * @param string  $old_status    Old post status.
		 * @param WP_Post $post          Post object.
		 */
		$valid_statuses = apply_filters( 'pno_count_cache_supported_statuses', array( 'pending' ), $new_status, $old_status, $post );
		$rlike          = array();

		// New status transient option name.
		if ( in_array( $new_status, $valid_statuses, true ) ) {
			$rlike[] = "^_transient_pno_{$new_status}_{$post->post_type}_count_user_";
		}

		// Old status transient option name.
		if ( in_array( $old_status, $valid_statuses, true ) ) {
			$rlike[] = "^_transient_pno_{$old_status}_{$post->post_type}_count_user_";
		}

		if ( empty( $rlike ) ) {
			return;
		}

		$transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM $wpdb->options WHERE option_name RLIKE %s",
				implode( '|', $rlike )
			)
		);

		// For each transient...
		foreach ( $transients as $transient ) {
			// Strip away the WordPress prefix in order to arrive at the transient key.
			$key = str_replace( '_transient_', '', $transient );
			// Now that we have the key, use WordPress core to the delete the transient.
			delete_transient( $key );
		}

		// Sometimes transients are not in the DB, so we have to do this too:.
		wp_cache_flush();
	}

	/**
	 * Get Listings Count from Cache
	 *
	 * @param string $post_type the post type to verify.
	 * @param string $status the status of the post type to count.
	 * @param bool   $force Force update cache.
	 *
	 * @return int
	 */
	public static function get_listings_count( $post_type = 'listings', $status = 'pending', $force = false ) {

		// Get user based cache transient.
		$user_id   = get_current_user_id();
		$transient = "pno_{$status}_{$post_type}_count_user_{$user_id}";

		// Set listings_count value from cache if exists, otherwise set to 0 as default.
		$cached_count = get_transient( $transient );
		$status_count = $cached_count ? $cached_count : 0;

		// $cached_count will be false if transient does not exist.
		if ( false === $cached_count || $force ) {
			$count_posts = wp_count_posts( $post_type, 'readable' );
			// Default to 0 $status if object does not have a value.
			$status_count = isset( $count_posts->$status ) ? $count_posts->$status : 0;
			set_transient( $transient, $status_count, DAY_IN_SECONDS * 7 );
		}

		return $status_count;
	}

}

Helper::init();
