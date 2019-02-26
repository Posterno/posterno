<?php
/**
 * Handles definition of settings for listing fields.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Field\Settings;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that registers and deals with the settings for the listings custom fields editor.
 */
class Listing {

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
			'general'     => esc_html__( 'General', 'posterno' ),
			'validation'  => esc_html__( 'Validation', 'posterno' ),
			'permissions' => esc_html__( 'Permissions', 'posterno' ),
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
	public function get_general_settings() {

		$settings      = [];
		$current_field = $this->get_current_field();

		$settings[] = Field::make( 'hidden', 'listing_field_priority' );
		$settings[] = Field::make( 'hidden', 'listing_field_is_default' );

		$settings[] = Field::make( 'select', 'listing_field_type', esc_html__( 'Field type', 'posterno' ) )
			->set_required()
			->add_options( pno_get_registered_field_types() )
			->set_help_text( esc_html__( 'The selected field type determines how the field will look onto the listing submission forms.', 'posterno' ) );

		$settings[] = Field::make( 'text', 'listing_field_taxonomy', esc_html__( 'Taxonomy ID', 'posterno' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_type',
						'value'   => [ 'term-select', 'term-multiselect', 'term-checklist', 'term-chain-dropdown' ],
						'compare' => 'IN',
					),
				)
			)
			->set_help_text( esc_html__( 'Enter the name of the taxonomy which terms will be displayed within the dropdown.', 'posterno' ) );

		$options_disabled_for       = [ 'listing_tags' ];
		$options_generator_disabled = false;
		if ( in_array( $current_field, $options_disabled_for, true ) ) {
			$options_generator_disabled = true;
		}

		if ( ! $options_generator_disabled ) {
			$settings[] = Field::make( 'complex', 'listing_field_selectable_options', esc_html__( 'Field selectable options', 'posterno' ) )
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
				->set_help_text( esc_html__( 'Add options for this field type.', 'posterno' ) )
				->add_fields(
					array(
						Field::make( 'text', 'option_title', esc_html__( 'Option title', 'posterno' ) )->set_help_text( esc_html__( 'Enter the title of this option.', 'posterno' ) ),
					)
				);
		}

		$disable_multiple_setting      = false;
		$multiple_setting_disabled_for = [ 'listing_featured_image' ];
		if ( in_array( $current_field, $multiple_setting_disabled_for ) ) {
			$disable_multiple_setting = true;
		}

		if ( ! $disable_multiple_setting ) {
			$settings[] = Field::make( 'checkbox', 'listing_field_file_is_multiple', esc_html__( 'Allow multiple files', 'posterno' ) )
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
				->set_help_text( esc_html__( 'Enable this option to allow users to upload multiple files through this field.', 'posterno' ) );
		}

		$settings[] = Field::make( 'checkbox', 'listing_field_disable_branch_nodes', esc_html__( 'Disable branch nodes', 'posterno' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_type',
						'value'   => 'term-chain-dropdown',
						'compare' => '=',
					),
				)
			)
			->set_help_text( esc_html__( 'Enable this option so that branch nodes (Parent terms) are collapsible and not selectable.', 'posterno' ) );

		$settings[] = Field::make( 'checkbox', 'listing_field_chain_is_multiple', esc_html__( 'Allow multiple values', 'posterno' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'listing_field_type',
						'value'   => 'term-chain-dropdown',
						'compare' => '=',
					),
				)
			)
			->set_help_text( esc_html__( 'Enable this option to allow multiple values to be selected through the dropdown.', 'posterno' ) );

		$settings[] = Field::make( 'text', 'listing_field_label', esc_html__( 'Custom form label', 'posterno' ) )
			->set_help_text( esc_html__( 'This text will be used as label within the listing submission forms. Leave blank to use the field title.', 'posterno' ) );

		$settings[] = Field::make( 'text', 'listing_field_placeholder', esc_html__( 'Placeholder', 'posterno' ) )
			->set_help_text( esc_html__( 'This text will appear within the field when empty. Leave blank if not needed.', 'posterno' ) );

		$settings[] = Field::make( 'textarea', 'listing_field_description', esc_html__( 'Field description', 'posterno' ) )
			->set_help_text( esc_html__( 'This is the text that appears as a description within the forms. Leave blank if not needed.', 'posterno' ) );

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
	public function get_validation_settings() {

		$settings = [];

		$settings[] = Field::make( 'checkbox', 'listing_field_is_required', esc_html__( 'Set as required', 'posterno' ) )
			->set_help_text( esc_html__( 'Enable this option so the field must be filled before the form can be processed.', 'posterno' ) );

		$settings[] = Field::make( 'text', 'listing_field_file_max_size', esc_html__( 'Upload max size:', 'posterno' ) )
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
			->set_help_text( esc_html__( 'Enter the maximum file size (in bytes) allowed for uploads through this field. Leave blank to use server settings.', 'posterno' ) );

		$settings[] = Field::make( 'multiselect', 'listing_field_file_extensions', esc_html__( 'Allowed file types:', 'posterno' ) )
			->add_options( 'pno_get_human_readable_mime_types' )
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
			->set_help_text( esc_html__( 'Specify which file types are supported by this field. Separate with comma to add multiple extensions. Eg: jpg, png.', 'posterno' ) );

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
	public function get_permissions_settings() {
		$settings = [];

		$disable_admin_only      = false;
		$current_field           = $this->get_current_field();
		$admin_only_disabled_for = [ 'listing_title' ];

		if ( in_array( $current_field, $admin_only_disabled_for ) ) {
			$disable_admin_only = true;
		}

		if ( ! $disable_admin_only ) {
			$settings[] = Field::make( 'checkbox', 'listing_field_is_admin_only', esc_html__( 'Admin only?', 'posterno' ) )
				->set_help_text( esc_html__( 'Enable this option to allow only administrators to customize the field. Hidden fields will not visible within the listing submission form on the frontend.', 'posterno' ) );
		}

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

		$datastore = new \PNO\Datastores\CustomFieldSettings();
		$datastore->set_custom_field_type( 'listing' );

		$container = Container::make( 'post_meta', esc_html__( 'Field settings', 'posterno' ) )
			->set_datastore( $datastore )
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

		Container::make( 'post_meta', esc_html__( 'Advanced', 'posterno' ) )
			->set_datastore( $datastore )
			->where( 'post_type', '=', 'pno_listings_fields' )
			->set_context( 'side' )
			->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'text', 'listing_field_meta_key', esc_html__( 'Unique meta key', 'posterno' ) )
						->set_required( true )
						->set_help_text( esc_html__( 'The key must be unique for each field and written in lowercase with an underscore ( _ ) separating words e.g country_list or job_title. This will be used to store information about the listings into the database of your website.', 'posterno' ) ),
				)
			);

	}

	/**
	 * Determine the field currently being edited in the admin panel.
	 *
	 * @return boolean|string
	 */
	private function get_current_field() {

		if ( ! is_admin() ) {
			return;
		}

		$field_id = isset( $_GET['post'] ) && is_admin() ? absint( $_GET['post'] ) : false; //phpcs:ignore
		$meta     = false;

		if ( $field_id ) {
			$listing_field = new \PNO\Field\Listing( $field_id );
			if ( $listing_field instanceof \PNO\Field\Listing ) {
				$meta = $listing_field->get_object_meta_key();
			}
		}

		return $meta;

	}

}

( new Listing() )->init();
