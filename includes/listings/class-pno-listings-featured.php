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
	 * @return void
	 */
	public function init() {

		add_action( 'carbon_fields_register_fields', [ $this, 'register_settings' ] );

	}

	/**
	 * Add a new metabox within the listings post type that holds the
	 * listings featured checkbox status.
	 *
	 * @return void
	 */
	public function register_settings() {

		Container::make( 'post_meta', esc_html__( 'Featured' ) )
			->where( 'post_type', '=', 'listings' )
			->set_context( 'side' )
			->set_priority( 'low' )
			->add_fields(
				array(
					Field::make( 'checkbox', 'listing_is_featured', esc_html__( 'Listing is featured' ) )
						->help_text( esc_html__( 'Featured listings will show at the top of the list during searches, and can be styled differently.' ) ),
				)
			);

	}

}

( new PNO_Listings_Featured() )->init();
