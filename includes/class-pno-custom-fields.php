<?php
/**
 * Handles registration and management of the custom fields settings.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The class that handles the custom fields settings.
 */
class PNO_Custom_Fields {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'carbon_fields_register_fields', [ $this, 'register_fields_settings' ] );
	}

	/**
	 * Register global settings for all fields.
	 *
	 * @return void
	 */
	public function register_fields_settings() {
		Container::make( 'post_meta', esc_html__( 'Main field settings' ) )
		->where( 'post_type', '=', 'pno_users_fields' )

		->add_tab(
			esc_html__( 'General' ), array(

				Field::make( 'text', 'field_label', esc_html__( 'Custom form label' ) )
					->set_help_text( esc_html__( 'This text will be used as label within the registration and account settings forms. Leave blank to use the field title.' ) ),

				Field::make( 'text', 'field_placeholder', esc_html__( 'Placeholder' ) )
					->set_help_text( esc_html__( 'This text will appear within the field when empty. Leave blank if not needed.' ) ),

				Field::make( 'textarea', 'field_description', esc_html__( 'Field description' ) )
					->set_help_text( esc_html__( 'This is the text that appears as a description within the forms. Leave blank if not needed.' ) ),

			)
		)
		->add_tab(
			esc_html__( 'Validation' ), array(

				Field::make( 'checkbox', 'field_is_required', esc_html__( 'Set as required' ) )
					->set_help_text( esc_html__( 'Enable this option so the field must be filled before the form can be processed.' ) ),

			)
		)
		->add_tab(
			esc_html__( 'Permissions' ), array(

				Field::make( 'checkbox', 'field_is_hidden', esc_html__( 'Admin only?' ) )
					->set_help_text( esc_html__( 'Enable this option to allow only administrators to customize the field. Hidden fields will not be customizable from the account settings page.' ) ),

				Field::make( 'checkbox', 'field_is_read_only', esc_html__( 'Set as read only' ) )
					->set_help_text( esc_html__( 'Enable to prevent users from editing this field but still make it visible within the account settings page.' ) ),

			)
		)
		->add_tab(
			esc_html__( 'User meta key' ), array(
				Field::make( 'text', 'field_meta_key', esc_html__( 'Unique meta key' ) )
					->set_required( true )
					->set_help_text( esc_html__( 'The key must be unique for each field and written in lowercase with an underscore ( _ ) separating words e.g country_list or job_title. This will be used to store information about your users into the database of your website.' ) ),
			)
		);

		Container::make( 'post_meta', '<span class="dashicons dashicons-admin-settings"></span>' )
		->where( 'post_type', '=', 'pno_users_fields' )
		->set_context( 'side' )
		->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'select', 'field_type', esc_html__( 'Field type' ) )
						->set_required()
						->add_options( pno_get_registered_field_types() ),
					Field::make( 'html', 'crb_field_type_info' )
						->set_html( '<div class="pno-field-type-notice">' . esc_html__( 'The selected field type determines how the field will look onto the account and registration forms.' ) . '<br/><br/>' . esc_html__( 'When the field type is changed, save the field to display settings related to the new field type if any available.' ) . '</div>' ),
				)
			);

		Container::make( 'post_meta', esc_html__( 'Advanced' ) )
		->where( 'post_type', '=', 'pno_users_fields' )
		->set_context( 'side' )
		->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'text', 'field_custom_classes', esc_html__( 'Custom css classes' ) )
					->set_help_text( esc_html__( 'Enter custom css classes to customize the style of the field. Leave blank if not needed.' ) ),
				)
			);

	}

}

new PNO_Custom_Fields;
