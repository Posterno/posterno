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
	public function init() {

		add_action( 'carbon_fields_register_fields', [ $this, 'register_type_settings' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'register_categories_settings' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'register_locations_settings' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'register_tags_settings' ] );

	}

	/**
	 * Get common settings available to all listings taxonomies.
	 *
	 * @return array
	 */
	private function get_common_settings() {

		$fields = [];

		$fields[] = Field::make( 'text', 'term_icon', esc_html__( 'Custom icon class', 'posterno' ) )
			->set_help_text( esc_html__( 'Custom css class of an icon. Eg: calendar-week', 'posterno' ) );

		$fields[] = Field::make( 'image', 'term_image', esc_html__( 'Featured image', 'posterno' ) )
			->set_value_type( 'url' )
			->set_help_text( esc_html__( 'The featured image is not prominent by default; however, some themes may show it.', 'posterno' ) );

		return $fields;

	}

	/**
	 * Adds settings to the listings types taxonomy.
	 *
	 * @return void
	 */
	public function register_type_settings() {

		$fields = [];

		if ( pno_get_option( 'submission_categories_associated', false ) ) {
			$fields[] = Field::make( 'multiselect', 'associated_categories', esc_html__( 'Associated categories', 'posterno' ) )
				->set_help_text( esc_html__( 'Select one or more listings categories that you wish to assign to this listing type.', 'posterno' ) )
				->add_options( 'pno_get_listings_categories_for_association' );
		}

		$fields = array_merge( $fields, $this->get_common_settings() );

		Container::make( 'term_meta', esc_html__( 'Listing type settings', 'posterno' ) )
			->where( 'term_taxonomy', '=', 'listings-types' )
			->add_fields( $fields );

	}

	/**
	 * Add settings to the listings categories taxonomy.
	 *
	 * @return void
	 */
	public function register_categories_settings() {

		if ( ! pno_get_option( 'submission_tags_associated', false ) ) {
			return;
		}

		$fields = [];

		if ( pno_get_option( 'submission_tags_associated', false ) ) {
			$fields[] = Field::make( 'multiselect', 'associated_tags', esc_html__( 'Associated listing tags', 'posterno' ) )
				->set_help_text( esc_html__( 'Select one or more listings tags that you wish to assign to this listing category.', 'posterno' ) )
				->add_options( 'pno_get_listings_tags_for_association' );
		}

		$fields = array_merge( $fields, $this->get_common_settings() );

		Container::make( 'term_meta', esc_html__( 'Listing categories settings', 'posterno' ) )
			->where( 'term_taxonomy', '=', 'listings-categories' )
			->add_fields( $fields );

	}

	/**
	 * Add settings to the listings locations taxonomy.
	 *
	 * @return void
	 */
	public function register_locations_settings() {

		Container::make( 'term_meta', esc_html__( 'Listing location settings', 'posterno' ) )
			->where( 'term_taxonomy', '=', 'listings-locations' )
			->add_fields( $this->get_common_settings() );

	}

	/**
	 * Add settings to the listings tags taxonomy.
	 *
	 * @return void
	 */
	public function register_tags_settings() {

		Container::make( 'term_meta', esc_html__( 'Listing tag settings', 'posterno' ) )
			->where( 'term_taxonomy', '=', 'listings-tags' )
			->add_fields( $this->get_common_settings() );

	}

}

( new PNO_Listing_Terms_Custom_Fields() )->init();
