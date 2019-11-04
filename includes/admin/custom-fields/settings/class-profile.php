<?php
/**
 * Handles definition of settings for profile fields.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Field\Settings;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class that handles settings registration for profiles custom fields.
 */
class Profile {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {

		global $pagenow;

		$pid = isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ? absint( $_GET['post'] ) : false;

		if ( is_admin() && get_post_type( $pid ) === 'listings' ) {
			return;
		}

		add_action( 'carbon_fields_register_fields', [ $this, 'register_settings' ] );

	}

	/**
	 * Retrieve the list of settings tabs for profile fields settings.
	 *
	 * @return array
	 */
	public function get_settings_tabs() {

		$tabs = [
			'general'     => esc_html__( 'General', 'posterno' ),
			'validation'  => esc_html__( 'Validation', 'posterno' ),
			'permissions' => esc_html__( 'Permissions', 'posterno' ),
		];

		/**
		 * Allow developers to customize the settings tabs for the profile custom fields post type.
		 *
		 * @param array $tabs the list of currently registered tabs.
		 * @return array
		 */
		return apply_filters( 'pno_profile_field_settings_tabs', $tabs );

	}

	/**
	 * Get the list of general settings for the profile custom fields.
	 *
	 * @return array
	 */
	public function get_general_settings() {

		$settings = [];

		$settings[] = Field::make( 'hidden', 'profile_field_priority' );
		$settings[] = Field::make( 'hidden', 'profile_is_default_field' );

		$settings[] = Field::make( 'select', 'profile_field_type', esc_html__( 'Field type', 'posterno' ) )
			->set_required()
			->add_options(
				pno_get_registered_field_types(
					[
						'social-profiles',
						'listing-category',
						'listing-tags',
						'term-select',
						'term-multiselect',
						'term-checklist',
						'term-chain-dropdown',
						'listing-opening-hours',
						'listing-location',
					]
				)
			)
			->set_help_text( esc_html__( 'The selected field type determines how the field will look onto the account and registration forms.', 'posterno' ) );

		$settings[] = Field::make( 'complex', 'profile_field_selectable_options', esc_html__( 'Field selectable options', 'posterno' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'profile_field_type',
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

		$field_id = isset( $_GET['post'] ) && is_admin() ? absint( $_GET['post'] ) : false; //phpcs:ignore
		$disable_multiple_setting = false;

		if ( $field_id ) {
			$profile_field = \PNO\Entities\Field\Profile::getFromID( $field_id );
			if ( $profile_field instanceof \PNO\Entities\Field\Profile && $profile_field->getObjectMetaKey() === 'avatar' ) {
				$disable_multiple_setting = true;
			}
		}

		if ( ! $disable_multiple_setting ) {
			$settings[] = Field::make( 'checkbox', 'profile_field_file_is_multiple', esc_html__( 'Allow multiple files', 'posterno' ) )
				->set_conditional_logic(
					array(
						'relation' => 'AND',
						array(
							'field'   => 'profile_field_type',
							'value'   => 'file',
							'compare' => '=',
						),
					)
				)
				->set_help_text( esc_html__( 'Enable this option to allow users to upload multiple files through this field.', 'posterno' ) );
		}

		$settings[] = Field::make( 'text', 'profile_field_label', esc_html__( 'Custom form label', 'posterno' ) )
			->set_help_text( esc_html__( 'This text will be used as label within the registration and account settings forms. Leave blank to use the field title.', 'posterno' ) );

		$settings[] = Field::make( 'text', 'profile_field_placeholder', esc_html__( 'Placeholder', 'posterno' ) )
			->set_help_text( esc_html__( 'This text will appear within the field when empty. Leave blank if not needed.', 'posterno' ) );

		$settings[] = Field::make( 'textarea', 'profile_field_description', esc_html__( 'Field description', 'posterno' ) )
			->set_help_text( esc_html__( 'This is the text that appears as a description within the forms. Leave blank if not needed.', 'posterno' ) );

		/**
		 * Allow developers to customize the settings for the profile field post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "General" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_profile_general_settings', $settings );
	}

	/**
	 * Get the list of validation settings for the profile custom fields.
	 *
	 * @return array
	 */
	public function get_validation_settings() {
		$settings = [];

		$settings[] = Field::make( 'checkbox', 'profile_field_is_required', esc_html__( 'Set as required', 'posterno' ) )
			->set_help_text( esc_html__( 'Enable this option so the field must be filled before the form can be processed.', 'posterno' ) );

		$settings[] = Field::make( 'text', 'profile_field_file_max_size', esc_html__( 'Upload max size:', 'posterno' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'profile_field_type',
						'value'   => 'file',
						'compare' => '=',
					),
				)
			)
			->set_help_text( esc_html__( 'Enter the maximum file size (in bytes) allowed for uploads through this field. Leave blank to use server settings.', 'posterno' ) );

		$settings[] = Field::make( 'multiselect', 'profile_field_file_extensions', esc_html__( 'Allowed file types:', 'posterno' ) )
			->add_options( 'pno_get_human_readable_mime_types' )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'profile_field_type',
						'value'   => 'file',
						'compare' => '=',
					),
				)
			)
			->set_help_text( esc_html__( 'Specify which file types are supported by this field.', 'posterno' ) );

		/**
		 * Allow developers to customize the settings for the profile field post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "Validation" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_profile_validation_settings', $settings );
	}

	/**
	 * Get the list of permissions settings for the profile custom fields.
	 *
	 * @return array
	 */
	public function get_permissions_settings() {

		$settings = [];

		$field_id = isset( $_GET['post'] ) && is_admin() ? absint( $_GET['post'] ) : false; //phpcs:ignore
		$disable_admin_only_setting = false;

		if ( $field_id ) {
			$profile_field = \PNO\Entities\Field\Profile::getFromID( $field_id );
			if ( $profile_field instanceof \PNO\Entities\Field\Profile && $profile_field->getObjectMetaKey() === 'avatar' ) {
				$disable_admin_only_setting = true;
			}
		}

		if ( ! $disable_admin_only_setting ) {
			$settings[] = Field::make( 'checkbox', 'profile_field_is_admin_only', esc_html__( 'Admin only?', 'posterno' ) )
				->set_help_text( esc_html__( 'Enable this option to allow only administrators to customize the field. Hidden fields will not be customizable from the account settings page.', 'posterno' ) );
		}

		$settings[] = Field::make( 'checkbox', 'profile_field_is_read_only', esc_html__( 'Set as read only', 'posterno' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'profile_field_type',
						'value'   => [ 'file', 'editor', 'heading' ],
						'compare' => 'NOT IN',
					),
				)
			)
			->set_help_text( esc_html__( 'Enable to prevent users from editing this field but still make it visible within the account settings page.', 'posterno' ) );

		$settings[] = Field::make( 'select', 'profile_field_visibility', esc_html__( 'Visibility', 'posterno' ) )
			->set_options(
				[
					'visible' => esc_html__( 'Visible', 'posterno' ),
					'hidden'  => esc_html__( 'Hidden', 'posterno' ),
				]
			)
			->set_help_text( esc_html__( 'Set the frontend visibility for this field.', 'posterno' ) );

		/**
		 * Allow developers to customize the settings for the profile field post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "Permissions" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_profile_permissions_settings', $settings );
	}

	/**
	 * Load settings into the post type window.
	 *
	 * @return void
	 */
	public function register_settings() {

		$datastore = new \PNO\Datastores\CustomFieldSettings();
		$datastore->set_custom_field_type( 'profile' );

		$container = Container::make( 'post_meta', 'pno_profile_fields_settings', esc_html__( 'Field settings', 'posterno' ) )
			->set_datastore( $datastore )
			->where( 'post_type', '=', 'pno_users_fields' );

		foreach ( $this->get_settings_tabs() as $key => $tab ) {

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
					$fields = apply_filters( "pno_profile_fields_{$key}_settings", $fields );
					break;
			}

			$container->add_tab( $tab, $fields );

		}

		Container::make( 'post_meta', 'pno_profile_fields_advanced_settings', esc_html__( 'Advanced', 'posterno' ) )
			->set_datastore( $datastore )
			->where( 'post_type', '=', 'pno_users_fields' )
			->set_context( 'side' )
			->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'text', 'profile_field_meta_key', esc_html__( 'Unique meta key', 'posterno' ) )
						->set_required( true )
						->set_help_text( esc_html__( 'The key must be unique for each field and written in lowercase with an underscore ( _ ) separating words e.g country_list or job_title. This will be used to store information about your users into the database of your website.', 'posterno' ) ),

					Field::make( 'text', 'profile_field_classes', esc_html__( 'Custom css classes', 'posterno' ) ),

				)
			);

	}

}

( new Profile() )->init();
