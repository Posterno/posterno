<?php
/**
 * Uninstall Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load the plugin back.
require_once 'posterno.php';

global $wpdb;

if ( pno_get_option( 'uninstall_on_delete', false ) ) {

	// Delete custom post types and taxonomies.
	$taxonomies = array( 'listings-types', 'listings-categories', 'listings-locations', 'listings-tags', 'pno-email-type' );
	$post_types = array( 'listings', 'pno_emails', 'pno_listings_fields', 'pno_users_fields', 'pno_signup_fields', 'pno_schema' );

	// Delete post types.
	foreach ( $post_types as $post_type ) {
		$taxonomies = array_merge( $taxonomies, get_object_taxonomies( $post_type ) );
		$items      = get_posts(
			array(
				'post_type'   => $post_type,
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true );
			}
		}
	}

	foreach ( array_unique( array_filter( $taxonomies ) ) as $taxonomy ) {
		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );
		// Delete Terms.
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}
		// Delete Taxonomies.
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}

	// Delete pages.
	$pno_created_pages = array(
		'login_page',
		'password_page',
		'registration_page',
		'dashboard_page',
		'submission_page',
		'editing_page',
		'profile_page',
	);
	foreach ( $pno_created_pages as $p ) {
		$page = pno_get_option( $p, false );
		if ( $page && is_array( $page ) && ! empty( $page ) ) {
			wp_delete_post( $page[0], true );
		}
	}

	// Delete all options.
	$pno_options = [
		'pno_activation_date',
		'pno_listings_fields_installed',
		'pno_profile_fields_installed',
		'pno_registration_fields_installed',
		'pno_settings',
		'pno_version',
		'posterno_dashboard_menu_installed',
		'posterno_emails_installed',
		'posterno_profile_menu_installed',
		'posterno_settings_installed',
		'wpdb_pno_listing_fields_version',
		'wpdb_pno_profile_fields_version',
		'wpdb_pno_registration_fields_version',
	];

	foreach ( $pno_options as $option ) {
		delete_option( $option );
	}

	// Remove all database tables.
	$pno_db_tables = array( 'listing_fields', 'profile_fields', 'registration_fields' );
	foreach ( $pno_db_tables as $table ) {
		$query = "DROP TABLE IF EXISTS {$wpdb->prefix}pno_{$table}";
		$wpdb->query( $query );
	}

	// Remove transients.
	\PNO\Cache\Helper::flush_all_fields_cache();
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_pno\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_pno\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_pno\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_pno\_%'" );

	// Remove cron jobs.
	posterno()->unschedule_cron_jobs();

}
