<?php
/**
 * Handles integration of settings for the listings custom fields.
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
 * The class that registers and deals with the settings for the listings custom fields editor.
 */
class PNO_Listings_Custom_Fields_Settings {

	/**
	 * Hook into WordPress and get things started.
	 *
	 * @return void
	 */
	public function init() {

		add_action( 'carbon_fields_register_fields', [ $this, 'register_settings' ] );

	}

	/**
	 * Retrieve the list of defined settings tabs for the listings custom fields editor.
	 *
	 * @return array
	 */
	public function get_registered_settings_tabs() {

		$tabs = [
			'general'     => esc_html__( 'General' ),
			'validation'  => esc_html__( 'Validation' ),
			'permissions' => esc_html__( 'Permissions' ),
		];

		/**
		 * Allow developers to register new tabs within the listing custom fields settings editor.
		 *
		 * @param array $tabs the list of tabs currently registered.
		 * @return array
		 */
		return apply_filters( 'pno_listings_custom_fields_settings_tabs', $tabs );

	}

	/**
	 * Get the list of general settings for the listings custom fields.
	 *
	 * @return array
	 */
	public static function get_general_settings() {

		$settings = [];

		$settings[] = Field::make( 'hidden', 'listing_field_priority' );
		$settings[] = Field::make( 'hidden', 'listing_field_is_default' );

		$settings[] = Field::make( 'select', 'listing_field_type', esc_html__( 'Field type' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_is_default',
						'value'   => true,
						'compare' => '!=',
					),
				)
			)
			->set_required()
			->add_options( pno_get_registered_field_types( [
				'social-profiles',
				'listing-category',
				'listing-tags',
				'opening-hours',
				'listing-location',
			] ) )
			->set_help_text( esc_html__( 'The selected field type determines how the field will look onto the listing submission forms.' ) );

		$settings[] = Field::make( 'text', 'listing_field_taxonomy', esc_html__( 'Taxonomy ID' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_is_default',
						'value'   => true,
						'compare' => '!=',
					),
					array(
						'field'   => 'listing_field_type',
						'value'   => 'term-select',
						'compare' => '=',
					),
				)
			)
			->set_help_text( esc_html__( 'Enter the name of the taxonomy which terms will be displayed within the dropdown.' ) );

		$settings[] = Field::make( 'text', 'listing_field_dropzone_max_files', esc_html__( 'Dropzone max files' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_is_default',
						'value'   => true,
						'compare' => '!=',
					),
					array(
						'field'   => 'listing_field_type',
						'value'   => 'dropzone',
						'compare' => '=',
					),
				)
			)
			->set_help_text( esc_html__( 'Specify the maximum amount of files that can be uploaded through this field.' ) );

		$settings[] = Field::make( 'text', 'listing_field_dropzone_max_size', esc_html__( 'Dropzone max size' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_is_default',
						'value'   => true,
						'compare' => '!=',
					),
					array(
						'field'   => 'listing_field_type',
						'value'   => 'dropzone',
						'compare' => '=',
					),
				)
			)
			->set_help_text( esc_html__( 'Enter the maximum file size (in MB) allowed for uploads through this field. Leave blank to use server settings.' ) );

		$settings[] = Field::make( 'complex', 'listing_field_selectable_options', esc_html__( 'Field selectable options' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_type',
						'value'   => pno_get_multi_options_field_types(),
						'compare' => 'IN',
					),
				)
			)
			->set_layout( 'tabbed-vertical' )
			->set_help_text( esc_html__( 'Add options for this field type.' ) )
			->add_fields(
				array(
					Field::make( 'text', 'option_title', esc_html__( 'Option title' ) )->set_help_text( esc_html__( 'Enter the title of this option.' ) ),
				)
			);

		$settings[] = Field::make( 'text', 'listing_field_label', esc_html__( 'Custom form label' ) )
			->set_help_text( esc_html__( 'This text will be used as label within the listing submission forms. Leave blank to use the field title.' ) );

		$settings[] = Field::make( 'text', 'listing_field_placeholder', esc_html__( 'Placeholder' ) )
			->set_help_text( esc_html__( 'This text will appear within the field when empty. Leave blank if not needed.' ) );

		$settings[] = Field::make( 'textarea', 'listing_field_description', esc_html__( 'Field description' ) )
			->set_help_text( esc_html__( 'This is the text that appears as a description within the forms. Leave blank if not needed.' ) );

		$settings[] = Field::make( 'text', 'listing_field_file_max_size', esc_html__( 'Upload max size:' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_type',
						'value'   => 'file',
						'compare' => '=',
					),
				)
			)
			->set_help_text( esc_html__( 'Enter the maximum file size (in bytes) allowed for uploads through this field. Leave blank to use server settings.' ) );

		/**
		 * Allow developers to customize the settings for the listings fields post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "General" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_listings_fields_general_settings', $settings );
	}

	/**
	 * Get the list of validation settings for the listings custom fields.
	 *
	 * @return array
	 */
	public static function get_validation_settings() {
		$settings = [];

		$settings[] = Field::make( 'checkbox', 'listing_field_is_required', esc_html__( 'Set as required' ) )
			->set_help_text( esc_html__( 'Enable this option so the field must be filled before the form can be processed.' ) );

		/**
		 * Allow developers to customize the settings for the listings custom fields post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "Validation" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_listings_fields_validation_settings', $settings );
	}

	/**
	 * Get the list of permissions settings for the listings custom fields.
	 *
	 * @return array
	 */
	public static function get_permissions_settings() {
		$settings = [];

		$settings[] = Field::make( 'checkbox', 'listing_field_is_hidden', esc_html__( 'Admin only?' ) )
			->set_help_text( esc_html__( 'Enable this option to allow only administrators to customize the field. Hidden fields will not visible within the listing submission form on the frontend.' ) );

		/**
		 * Allow developers to customize the settings for the listings custom fields post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "Permissions" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_listings_fields_permissions_settings', $settings );
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings() {

		$container = Container::make( 'post_meta', esc_html__( 'Field settings' ) )
			->set_datastore( new PNO\Datastores\DataCompressor() )
			->where( 'post_type', '=', 'pno_listings_fields' );

		foreach ( $this->get_registered_settings_tabs() as $key => $tab ) {

			$fields = [];

			switch ( $key ) {
				case 'general':
				case 'validation':
				case 'permissions':
					$fields = $this->{"get_{$key}_settings"}();
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
					$fields = apply_filters( "pno_listings_fields_{$key}_settings", $fields );
					break;
			}

			$container->add_tab( $tab, $fields );

		}

		Container::make( 'post_meta', esc_html__( 'Advanced' ) )
			->where( 'post_type', '=', 'pno_listings_fields' )
			->set_context( 'side' )
			->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'text', 'listing_field_meta_key', esc_html__( 'Unique meta key' ) )
						->set_required( true )
						->set_help_text( esc_html__( 'The key must be unique for each field and written in lowercase with an underscore ( _ ) separating words e.g country_list or job_title. This will be used to store information about the listings into the database of your website.' ) ),
					Field::make( 'text', 'listing_field_custom_classes', esc_html__( 'Custom css classes' ) )
						->set_datastore( new PNO\Datastores\DataCompressor() )
						->set_help_text( esc_html__( 'Enter custom css classes to customize the style of the field. Leave blank if not needed.' ) ),
				)
			);

	}

}

( new PNO_Listings_Custom_Fields_Settings() )->init();
