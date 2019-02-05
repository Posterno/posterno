<?php
/**
 * Handles integration of custom fields for the registration form.
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
 * Class that handles settings creation for registration fields.
 */
class Registration {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'carbon_fields_register_fields', [ $this, 'register_settings' ] );
	}

	/**
	 * Retrieve the list of settings tabs for registration fields settings.
	 *
	 * @return array
	 */
	public function get_settings_tabs() {

		$tabs = [
			'general' => esc_html__( 'General', 'posterno' ),
		];

		/**
		 * Allow developers to customize the settings tabs for the registration custom fields post type.
		 *
		 * @param array $tabs the list of currently registered tabs.
		 * @return array
		 */
		return apply_filters( 'pno_registration_field_settings_tabs', $tabs );

	}

	/**
	 * Get a list of settings for the general tab.
	 *
	 * @return array
	 */
	public function get_general_settings() {

		$settings = [];

		$settings[] = Field::make( 'hidden', 'registration_field_is_default' );
		$settings[] = Field::make( 'hidden', 'registration_field_priority' );

		$settings[] = Field::make( 'hidden', 'registration_field_profile_field_id' )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'registration_field_is_default',
						'value'   => [ 'password', 'username', 'email' ],
						'compare' => 'NOT IN',
					),
				)
			);

		$settings[] = Field::make( 'checkbox', 'registration_field_is_required', esc_html__( 'Set as required', 'posterno' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'registration_field_is_default',
						'value'   => [ 'email' ],
						'compare' => 'NOT IN',
					),
				)
			)
			->set_help_text( esc_html__( 'Enable this option so the field must be filled before the form can be processed.', 'posterno' ) );

		$settings[] = Field::make( 'text', 'registration_field_label', esc_html__( 'Custom form label', 'posterno' ) )
			->set_help_text( esc_html__( 'This text will be used as label within the registration forms. Leave blank to use the field title.', 'posterno' ) );

		$settings[] = Field::make( 'text', 'registration_field_placeholder', esc_html__( 'Placeholder', 'posterno' ) )
			->set_help_text( esc_html__( 'This text will appear within the field when empty. Leave blank if not needed.', 'posterno' ) );

		$settings[] = Field::make( 'textarea', 'registration_field_description', esc_html__( 'Field description', 'posterno' ) )
			->set_help_text( esc_html__( 'This is the text that appears as a description within the forms. Leave blank if not needed.', 'posterno' ) );

		/**
		 * Allow developers to customize the settings for the registration field post type.
		 * Settings are powered by Carbon Fields and will be displayed within the "General" tab.
		 *
		 * @param array $settings list of Carbon Fields - fields.
		 * @return array
		 */
		return apply_filters( 'pno_registration_fields_general_settings', $settings );

	}

	/**
	 * Register the settings for the registration fields.
	 *
	 * @return void
	 */
	public function register_settings() {

		$datastore = new \PNO\Datastores\CustomFieldSettings();
		$datastore->set_custom_field_type( 'registration' );

		$container = Container::make( 'post_meta', esc_html__( 'Field settings', 'posterno' ) )
			->set_datastore( $datastore )
			->where( 'post_type', '=', 'pno_signup_fields' );

		foreach ( self::get_settings_tabs() as $key => $tab ) {

			$fields = [];

			switch ( $key ) {
				case 'general':
					$fields = self::{"get_{$key}_settings"}();
					break;
				default:
					/**
					 * Allows developers to define custom settings for the registration fields post type
					 * of a given setting tab.
					 *
					 * Where $key is the key of the custom tab you've added.
					 *
					 * @param array $fields the array where you're going to add the fields for the custom tab.
					 * @return array
					 */
					$fields = apply_filters( "pno_registration_fields_{$key}_settings", $fields );
					break;
			}

			$container->add_tab( $tab, $fields );

		}

	}

}

( new Registration() )->init();
