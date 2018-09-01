<?php
/**
 * Handles integration of custom fields for listings.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The class that handles custom fields integration.
 */
class PNO_Listings_Custom_Fields {

	/**
	 * Hook into WordPress and register custom fields.
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'carbon_fields_register_fields', [ __class__, 'register_listings_settings' ] );

	}

	/**
	 * Register all the custom fields related to listings.
	 *
	 * @return void
	 */
	public static function register_listings_settings() {

		Container::make( 'post_meta', esc_html__( 'Listing settings' ) )
			->where( 'post_type', '=', 'listings' )
			->add_tab(
				esc_html__( 'Details' ),
				array(
					Field::make( 'text', 'listing_phone_number', esc_html__( 'Phone number' ) ),
					Field::make( 'text', 'listing_email', esc_html__( 'Email address' ) ),
					Field::make( 'text', 'listing_website', esc_html__( 'Website' ) ),
				)
			)
			->add_tab(
				esc_html__( 'Location' ),
				array(
					Field::make( 'map', 'listing_location', esc_html__( 'Location' ) )
						->set_help_text( esc_html__( 'Search an address or drag and drop the pin on the map to select location.' ) ),
					Field::make( 'text', 'listing_zipcode', esc_html__( 'Zipcode' ) ),
				)
			)
			->add_tab(
				esc_html__( 'Media' ),
				array(
					Field::make( 'media_gallery', 'listing_gallery_images', esc_html__( 'Images' ) )
						->set_type( array( 'image' ) ),
					Field::make( 'oembed', 'listing_media_embed', esc_html__( 'Embed' ) )
						->set_help_text(
							sprintf(
								__( 'Embed videos, images, tweets, audio, and other content into the listing by simply providing the url of the source. <a href="%s" target="_blank">View list of supported embeds.</a>' ),
								'https://codex.wordpress.org/Embeds'
							)
						),
				)
			);

	}

}

( new PNO_Listings_Custom_Fields() )->init();
