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
						->add_options( [] ),
				)
			);

	}

}

( new PNO_Listing_Terms_Custom_Fields() )->init();
