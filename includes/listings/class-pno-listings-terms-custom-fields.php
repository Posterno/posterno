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

		add_action( 'carbon_fields_register_fields', [ __class__, 'register_type_settings' ] );
		add_action( 'carbon_fields_register_fields', [ __class__, 'register_categories_settings' ] );

	}

	/**
	 * Adds settings to the listings types taxonomy.
	 *
	 * @return void
	 */
	public static function register_type_settings() {

		if ( ! pno_get_option( 'submission_categories_associated', false ) ) {
			return;
		}

		Container::make( 'term_meta', esc_html__( 'Listing type settings', 'posterno' ) )
			->where( 'term_taxonomy', '=', 'listings-types' )
			->add_fields(
				array(
					Field::make( 'multiselect', 'associated_categories', esc_html__( 'Associated categories', 'posterno' ) )
						->set_help_text( esc_html__( 'Select one or more listings categories that you wish to assign to this listing type.', 'posterno' ) )
						->add_options( 'pno_get_listings_categories_for_association' ),
				)
			);

	}

	/**
	 * Add settings to the listings categories taxonomy.
	 *
	 * @return void
	 */
	public static function register_categories_settings() {

		Container::make( 'term_meta', esc_html__( 'Listing categories settings', 'posterno' ) )
			->where( 'term_taxonomy', '=', 'listings-categories' )
			->add_fields(
				[
					Field::make( 'multiselect', 'associated_tags', esc_html__( 'Associated listing tags', 'posterno' ) )
						->set_help_text( esc_html__( 'Select one or more listings tags that you wish to assign to this listing category.', 'posterno' ) )
						->add_options( 'pno_get_listings_tags_for_association' ),
				]
			);

	}

}

( new PNO_Listing_Terms_Custom_Fields() )->init();
