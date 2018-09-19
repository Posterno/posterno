<?php
/**
 * Handles integration of custom fields for listings terms.
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
 * The class that handles integration of fields.
 */
class PNO_Listing_Terms_Custom_Fields {

	/**
	 * Hook into WordPress and register custom fields.
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'carbon_fields_register_fields', [ __class__, 'register_categories_settings' ] );
		add_action( 'carbon_fields_register_fields', [ __class__, 'register_tags_settings' ] );

	}

	/**
	 * Adds settings to the listings categories taxonomy.
	 *
	 * @return void
	 */
	public static function register_categories_settings() {

		Container::make( 'term_meta', esc_html__( 'Listing category settings' ) )
			->where( 'term_taxonomy', '=', 'listings-categories' )
			->add_fields(
				array(
					Field::make( 'multiselect', 'associated_types', esc_html__( 'Associated listing types' ) )
						->set_datastore( new PNO\Datastores\ListingTermAssociation() )
						->set_help_text( esc_html__( 'Select one or more listings types if you wish to restrict this category to the selected types.' ) )
						->add_options( 'pno_get_listings_types_for_association' ),
				)
			);

	}

	/**
	 * Add settings to the listings tags taxonomy.
	 *
	 * @return void
	 */
	public static function register_tags_settings() {

		Container::make( 'term_meta', esc_html__( 'Listing tag settings' ) )
			->where( 'term_taxonomy', '=', 'listings-tags' )
			->add_fields( self::get_tags_settings() );

	}

	/**
	 * Retrieve the list of custom fields ( settings ) for listings tags. Powered by Carbon Fields.
	 *
	 * @return array
	 */
	public static function get_tags_settings() {

		$settings = [];

		$settings[] = Field::make( 'multiselect', 'associated_categories', esc_html__( 'Associated listing categories' ) )
			->set_help_text( esc_html__( 'Select one or more listings categories if you wish to restrict this tag to the selected categories.' ) )
			->add_options( 'pno_get_listings_categories_for_association' );

		/**
		 * Allows developers to customize the settings for listings tags.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_tags_settings', $settings );

	}

}

( new PNO_Listing_Terms_Custom_Fields() )->init();
