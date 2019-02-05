<?php
/**
 * Handles marking of listings as featured and appropriate ordering in queries.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles featuring of listings.
 */
class PNO_Listings_Featured {

	/**
	 * Hook into WordPress.
	 *
	 * @return mixed
	 */
	public function init() {

		if ( ! pno_listings_can_be_featured() ) {
			return false;
		}

		add_action( 'carbon_fields_register_fields', [ $this, 'register_settings' ] );
		add_action( 'carbon_fields_post_meta_container_saved', [ $this, 'update_featured_status' ] );

	}

	/**
	 * Add a new metabox within the listings post type that holds the
	 * listings featured checkbox status.
	 *
	 * @return void
	 */
	public function register_settings() {

		Container::make( 'post_meta', esc_html__( 'Featured', 'posterno' ) )
			->where( 'post_type', '=', 'listings' )
			->set_context( 'side' )
			->set_priority( 'low' )
			->add_fields(
				array(
					Field::make( 'checkbox', 'listing_is_featured', esc_html__( 'Listing is featured', 'posterno' ) )
						->help_text( esc_html__( 'Featured listings will show at the top of the list during searches, and can be styled differently.', 'posterno' ) ),
				)
			);

	}

	/**
	 * Detect changes into the carbon fields container and trigger the update function for
	 * the featuring status of listings.
	 *
	 * @param string $listing_id the listing to update.
	 * @return mixed
	 */
	public function update_featured_status( $listing_id ) {

		if ( get_post_type( $listing_id ) !== 'listings' ) {
			return false;
		}

		$featured = get_post_meta( $listing_id, '_listing_is_featured', true );

		self::maybe_update_menu_order( $listing_id, $featured );

	}

	/**
	 * Maybe sets menu_order if the featured status of a listing is changed.
	 *
	 * @param string $listing_id id of the listing to update.
	 * @param mixed  $meta_value the value set.
	 * @return void
	 */
	public static function maybe_update_menu_order( $listing_id, $meta_value ) {

		global $wpdb;

		if ( $meta_value === 'yes' || $meta_value === true ) {

			$wpdb->update(
				$wpdb->posts,
				array( 'menu_order' => -1 ),
				array( 'ID' => $listing_id )
			);

		} else {

			$wpdb->update(
				$wpdb->posts,
				array( 'menu_order' => 0 ),
				array(
					'ID'         => $listing_id,
					'menu_order' => -1,
				)
			);

		}

		clean_post_cache( $listing_id );

	}

}

( new PNO_Listings_Featured() )->init();
