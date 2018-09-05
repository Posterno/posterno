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
	 * Contains translatable text.
	 *
	 * @var string
	 */
	public static $opening_at = '';

	/**
	 * Contains translatable text.
	 *
	 * @var string
	 */
	public static $closing_at = '';

	/**
	 * Get the class started.
	 */
	public function __construct() {

		self::$opening_at = esc_html__( 'Opening at:' );
		self::$closing_at = esc_html__( 'Closing at:' );

	}

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

		$social_profiles_labels = array(
			'plural_name'   => esc_html__( 'Profiles' ),
			'singular_name' => esc_html__( 'Profile' ),
		);

		Container::make( 'post_meta', esc_html__( 'Listing settings' ) )
			->where( 'post_type', '=', 'listings' )
			->add_tab(
				esc_html__( 'Details' ),
				array(
					Field::make( 'text', 'listing_phone_number', esc_html__( 'Phone number' ) )->set_width( 33.33 ),
					Field::make( 'text', 'listing_email', esc_html__( 'Email address' ) )->set_width( 33.33 ),
					Field::make( 'text', 'listing_website', esc_html__( 'Website' ) )->set_width( 33.33 ),
					Field::make( 'complex', 'listing_social_profiles', esc_html__( 'Social profiles' ) )
						->set_datastore( new PNO\Datastores\SerializeField() )
						->setup_labels( $social_profiles_labels )
						->set_collapsed( true )
						->add_fields(
							array(
								Field::make( 'select', 'social_id', esc_html__( 'Network' ) )
									->add_options( 'pno_get_listings_registered_social_media' )
									->set_width( 50 ),
								Field::make( 'text', 'social_url', esc_html__( 'Profile url' ) )->set_width( 50 ),
							)
						),
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
						->set_datastore( new PNO\Datastores\SerializeField() )
						->set_type( array( 'image' ) ),
					Field::make( 'oembed', 'listing_media_embed', esc_html__( 'Embed' ) )
						->set_help_text(
							sprintf(
								__( 'Embed videos, images, tweets, audio, and other content into the listing by simply providing the url of the source. <a href="%s" target="_blank">View list of supported embeds.</a>' ),
								'https://codex.wordpress.org/Embeds'
							)
						),
				)
			)
			->add_tab(
				esc_html__( 'Opening Hours' ),
				self::get_days_fields()
			);

	}

	/**
	 * Generates custom fields for all days of the week.
	 *
	 * @return array
	 */
	private static function get_days_fields() {

		$days = pno_get_days_of_the_week();

		$fields = [];

		foreach ( $days as $day_string => $day_name ) {

			$fields[] = Field::make( 'html', $day_string )
				->set_html( '<h4>' . ucfirst( esc_html( $day_name ) ) . '</h4>' )
				->set_classes( 'inline-metabox-message' );

			$fields[] = Field::make( 'time', $day_string . '_opening', false )
				->set_datastore( new PNO\Datastores\OpeningHours() )
				->set_attribute( 'placeholder', self::$opening_at )
				->set_picker_options( self::get_timepicker_config() )
				->set_width( 50 );

			$fields[] = Field::make( 'time', $day_string . '_closing', false )
				->set_datastore( new PNO\Datastores\OpeningHours() )
				->set_attribute( 'placeholder', self::$closing_at )
				->set_picker_options( self::get_timepicker_config() )
				->set_width( 50 );

			$fields[] = Field::make( 'complex', $day_string . '_additional_times', esc_html__( 'Additional timings' ) )
				->set_datastore( new PNO\Datastores\OpeningHours() )
				->set_collapsed( true )
				->add_fields(
					array(
						Field::make( 'time', $day_string . '_opening', false )
							->set_attribute( 'placeholder', self::$opening_at )
							->set_picker_options( self::get_timepicker_config() )
							->set_width( 50 ),
						Field::make( 'time', $day_string . '_closing', false )
						->set_attribute( 'placeholder', self::$closing_at )
						->set_picker_options( self::get_timepicker_config() )
						->set_width( 50 ),
					)
				);

		}

		return $fields;

	}

	/**
	 * Additional settings for timepickers within the listings settings.
	 *
	 * @return array
	 */
	private static function get_timepicker_config() {

		return [
			'enableSeconds' => false,
		];

	}

}

( new PNO_Listings_Custom_Fields() )->init();
