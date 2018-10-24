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
	public $opening_at = '';

	/**
	 * Contains translatable text.
	 *
	 * @var string
	 */
	public $closing_at = '';

	/**
	 * Get the class started.
	 */
	public function __construct() {

		$this->opening_at = esc_html__( 'Opening at:' );
		$this->closing_at = esc_html__( 'Closing at:' );

	}

	/**
	 * Hook into WordPress and register custom fields.
	 *
	 * @return void
	 */
	public function init() {

		add_action( 'carbon_fields_register_fields', [ $this, 'register_listings_settings' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'register_custom_fields' ] );

	}

	/**
	 * Register all the custom fields related to listings.
	 *
	 * @return void
	 */
	public function register_listings_settings() {

		$social_profiles_labels = array(
			'plural_name'   => esc_html__( 'Profiles' ),
			'singular_name' => esc_html__( 'Profile' ),
		);

		$container = Container::make( 'post_meta', esc_html__( 'Listing settings' ) )
			->where( 'post_type', '=', 'listings' );

		foreach ( $this->get_listing_settings_tabs() as $key => $tab ) {

			$fields = [];

			switch ( $key ) {
				case 'details':
				case 'location':
				case 'media':
					$fields = $this->{"get_listing_{$key}_settings"}();
					break;
				case 'hours':
					$fields = $this->get_days_fields();
					break;
				default:
					/**
					 * Allows developers to define custom settings for the listings post type
					 * of a given setting tab.
					 *
					 * Where $key is the key of the custom tab you've added.
					 *
					 * @param array $fields the array where you're going to add the fields for the custom tab.
					 * @return array
					 */
					$fields = apply_filters( "pno_listing_{$key}_settings", $fields );
					break;
			}

			$container->add_tab( $tab, $fields );

		}

	}

	/**
	 * Get the list of tabs for the listing post type custom fields container.
	 * Powered by Carbon Fields.
	 *
	 * @return array
	 */
	public function get_listing_settings_tabs() {

		$tabs = [
			'details'  => esc_html__( 'Details' ),
			'location' => esc_html__( 'Location' ),
			'media'    => esc_html__( 'Media' ),
			'hours'    => esc_html__( 'Opening Hours' ),
		];

		/**
		 * Allow developers to register new tabs within the listing post type settings.
		 *
		 * @param array $tabs the list of tabs currently registered.
		 * @return array
		 */
		return apply_filters( 'pno_listing_settings_tabs', $tabs );

	}

	/**
	 * Define the list of settings for the listings post type.
	 * These settings are related to the "Details" tab.
	 *
	 * @return array
	 */
	public function get_listing_details_settings() {

		$social_profiles_labels = array(
			'plural_name'   => esc_html__( 'Profiles' ),
			'singular_name' => esc_html__( 'Profile' ),
		);

		$settings = [];

		$settings[] = Field::make( 'radio', 'listing_type', esc_html__( 'Listing type' ) )
			->set_datastore( new PNO\Datastores\ListingType() )
			->set_required( true )
			->set_classes( 'inline-radio-selector' )
			->add_options(
				'pno_get_listings_types_for_association'
			);

		$settings[] = Field::make( 'text', 'listing_phone_number', esc_html__( 'Phone number' ) )->set_width( 33.33 );
		$settings[] = Field::make( 'text', 'listing_email', esc_html__( 'Email address' ) )->set_width( 33.33 );
		$settings[] = Field::make( 'text', 'listing_website', esc_html__( 'Website' ) )->set_width( 33.33 );

		$settings[] = Field::make( 'complex', 'listing_social_profiles', esc_html__( 'Social profiles' ) )
			->set_datastore( new PNO\Datastores\SerializeComplexField() )
			->setup_labels( $social_profiles_labels )
			->set_collapsed( true )
			->add_fields(
				array(
					Field::make( 'select', 'social_id', esc_html__( 'Network' ) )
						->add_options( 'pno_get_registered_social_media' )
						->set_width( 50 ),
					Field::make( 'text', 'social_url', esc_html__( 'Profile url' ) )->set_width( 50 ),
				)
			);

		/**
		 * Allow developers to customize the settings for the listings post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "Details" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_listing_details_settings', $settings );

	}

	/**
	 * Define the list of settings for the listings post type.
	 * These settings are related to the "Location" tab.
	 *
	 * @return array
	 */
	public function get_listing_location_settings() {

		$settings = [];

		$settings[] = Field::make( 'map', 'listing_location', esc_html__( 'Location' ) )
			->set_help_text( esc_html__( 'Search an address or drag and drop the pin on the map to select location.' ) );

		$settings[] = Field::make( 'text', 'listing_zipcode', esc_html__( 'Zipcode' ) );

		/**
		 * Allow developers to customize the settings for the listings post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "Location" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_listing_location_settings', $settings );

	}

	/**
	 * Define the list of settings for the listings post type.
	 * These settings are related to the "Media" tab.
	 *
	 * @return array
	 */
	public function get_listing_media_settings() {

		$settings = [];

		$settings[] = Field::make( 'media_gallery', 'listing_gallery_images', esc_html__( 'Images' ) )
			->set_datastore( new PNO\Datastores\SerializeComplexField() )
			->set_type( array( 'image' ) );

		$settings[] = Field::make( 'oembed', 'listing_media_embed', esc_html__( 'Embed' ) )
			->set_help_text(
				sprintf(
					__( 'Embed videos, images, tweets, audio, and other content into the listing by simply providing the url of the source. <a href="%s" target="_blank">View list of supported embeds.</a>' ),
					'https://codex.wordpress.org/Embeds'
				)
			);

		/**
		 * Allow developers to customize the settings for the listings post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "Media" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_listing_media_settings', $settings );

	}

	/**
	 * Generates custom fields for all days of the week.
	 *
	 * @return array
	 */
	private function get_days_fields() {

		$days = pno_get_days_of_the_week();

		$fields = [];

		foreach ( $days as $day_string => $day_name ) {

			$fields[] = Field::make( 'html', $day_string )
				->set_html( '<h4>' . ucfirst( esc_html( $day_name ) ) . '</h4>' )
				->set_classes( 'inline-metabox-message' );

			$fields[] = Field::make( 'radio', $day_string . '_time_slots', '' )
				->set_datastore( new PNO\Datastores\HoursOfOperation() )
				->set_classes( 'inline-radio-selector' )
				->add_options( 'pno_get_listing_time_slots' );

			$fields[] = Field::make( 'time', $day_string . '_opening', '' )
				->set_conditional_logic(
					array(
						'relation' => 'AND',
						array(
							'field'   => $day_string . '_time_slots',
							'value'   => 'hours',
							'compare' => '=',
						),
					)
				)
				->set_datastore( new PNO\Datastores\OpeningHours() )
				->set_attribute( 'placeholder', $this->opening_at )
				->set_storage_format( 'H:i' )
				->set_input_format( 'H:i', 'H:i' )
				->set_picker_options( $this->get_timepicker_config() )
				->set_width( 50 );

			$fields[] = Field::make( 'time', $day_string . '_closing', '' )
				->set_conditional_logic(
					array(
						'relation' => 'AND',
						array(
							'field'   => $day_string . '_time_slots',
							'value'   => 'hours',
							'compare' => '=',
						),
					)
				)
				->set_datastore( new PNO\Datastores\OpeningHours() )
				->set_attribute( 'placeholder', $this->closing_at )
				->set_storage_format( 'H:i' )
				->set_input_format( 'H:i', 'H:i' )
				->set_picker_options( $this->get_timepicker_config() )
				->set_width( 50 );

			$fields[] = Field::make( 'complex', $day_string . '_additional_times', esc_html__( 'Additional timings' ) )
				->set_conditional_logic(
					array(
						'relation' => 'AND',
						array(
							'field'   => $day_string . '_time_slots',
							'value'   => 'hours',
							'compare' => '=',
						),
					)
				)
				->set_datastore( new PNO\Datastores\OpeningHours() )
				->set_collapsed( true )
				->add_fields(
					array(
						Field::make( 'time', $day_string . '_opening', '' )
							->set_attribute( 'placeholder', $this->opening_at )
							->set_picker_options( $this->get_timepicker_config() )
							->set_storage_format( 'H:i' )
							->set_input_format( 'H:i', 'H:i' )
							->set_width( 50 ),
						Field::make( 'time', $day_string . '_closing', '' )
							->set_attribute( 'placeholder', $this->closing_at )
							->set_picker_options( $this->get_timepicker_config() )
							->set_storage_format( 'H:i' )
							->set_input_format( 'H:i', 'H:i' )
							->set_width( 50 ),
					)
				);

		}

		/**
		 * Allow developers to customize the settings for the listings post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "Opening hours" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_listing_opening_hours_settings', $fields );

	}

	/**
	 * Additional settings for timepickers within the listings settings.
	 *
	 * @return array
	 */
	private function get_timepicker_config() {

		return [
			'enableSeconds' => false,
		];

	}

	/**
	 * Register custom listings fields created through the editor.
	 *
	 * @return void
	 */
	public function register_custom_fields() {

		$fields_query = [
			'post_type'              => 'pno_listings_fields',
			'posts_per_page'         => 100,
			'nopaging'               => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'post_status'            => 'publish',
			'meta_query'             => array(
				array(
					'key'     => '_listing_field_meta_key',
					'value'   => pno_get_registered_default_meta_keys(),
					'compare' => 'NOT IN',
				),
			),
		];

		$fields       = new WP_Query( $fields_query );
		$admin_fields = [];

		if ( $fields->have_posts() ) {

			$found_fields = $fields->get_posts();

			foreach ( $found_fields as $field_id ) {
				$custom_field = new PNO_Listing_Field( $field_id );

				if ( $custom_field instanceof PNO_Listing_Field && ! empty( $custom_field->get_meta() ) ) {

					$type = $custom_field->get_type();

					switch ( $type ) {
						case 'url':
						case 'email':
						case 'number':
						case 'password':
							$type = 'text';
							break;
						case 'multicheckbox':
							$type = 'set';
							break;
						case 'editor':
							$type = 'rich_text';
							break;
						case 'term-select':
							$type = 'select';
							break;
						case 'dropzone':
							$type = 'media_gallery';
							break;
					}

					if ( $type == 'select' || $type == 'set' || $type == 'multiselect' || $type == 'radio' ) {
						$admin_fields[] = Field::make( $type, $custom_field->get_meta(), $custom_field->get_name() )->add_options( $custom_field->get_selectable_options() );
					} elseif ( $type == 'file' ) {
						$admin_fields[] = Field::make( $type, $custom_field->get_meta(), $custom_field->get_name() )->set_value_type( 'url' );
					} elseif ( $custom_field->get_type() == 'number' ) {
						$admin_fields[] = Field::make( $type, $custom_field->get_meta(), $custom_field->get_name() )->set_attribute( 'type', 'number' );
					} elseif ( $custom_field->get_type() == 'password' ) {
						$admin_fields[] = Field::make( $type, $custom_field->get_meta(), $custom_field->get_name() )->set_attribute( 'type', 'password' );
					} else {
						$admin_fields[] = Field::make( $type, $custom_field->get_meta(), $custom_field->get_name() );
					}
				}
			}

			Container::make( 'post_meta', esc_html__( 'Additional settings' ) )
				->where( 'post_type', '=', 'listings' )
				->add_fields( $admin_fields );

		}

		wp_reset_postdata();

	}

	private function get_terms_options() {
		return [];
	}

}

( new PNO_Listings_Custom_Fields() )->init();
