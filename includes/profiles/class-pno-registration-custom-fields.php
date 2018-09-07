<?php
/**
 * Handles integration of custom fields for the registration form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class that handles settings creation for registration fields.
 */
class PNO_Registration_Custom_Fields {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'carbon_fields_register_fields', [ __class__, 'register_settings' ] );
	}

	/**
	 * Retrieve the list of settings tabs for registration fields settings.
	 *
	 * @return array
	 */
	public static function get_settings_tabs() {

		$tabs = [
			'general' => esc_html__( 'General' ),
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
	public static function get_general_settings() {

		$settings = [];

		$settings[] = Field::make( 'hidden', 'field_is_default' );
		$settings[] = Field::make( 'hidden', 'field_priority' );

		$settings[] = Field::make( 'text', 'field_profile_field_id', esc_html__( 'Profile field id' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'field_is_default',
						'value'   => [ 'password', 'username', 'email' ],
						'compare' => 'NOT IN',
					),
				)
			)
			->set_help_text( esc_html__( 'Registration fields must be mapped to an existing profile field in order to store information related to the field.' ) );

		$settings[] = Field::make( 'checkbox', 'field_is_required', esc_html__( 'Set as required' ) )
			->set_conditional_logic(
				array(
					'relation' => 'AND',
					array(
						'field'   => 'field_is_default',
						'value'   => [ 'email', 'password', 'username' ],
						'compare' => 'NOT IN',
					),
				)
			)
			->set_help_text( esc_html__( 'Enable this option so the field must be filled before the form can be processed.' ) );

		$settings[] = Field::make( 'text', 'field_label', esc_html__( 'Custom form label' ) )
			->set_help_text( esc_html__( 'This text will be used as label within the registration forms. Leave blank to use the field title.' ) );

		$settings[] = Field::make( 'text', 'field_placeholder', esc_html__( 'Placeholder' ) )
			->set_help_text( esc_html__( 'This text will appear within the field when empty. Leave blank if not needed.' ) );

		$settings[] = Field::make( 'textarea', 'field_description', esc_html__( 'Field description' ) )
			->set_help_text( esc_html__( 'This is the text that appears as a description within the forms. Leave blank if not needed.' ) );

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
	public static function register_settings() {

		$container = Container::make( 'post_meta', esc_html__( 'Field settings' ) )
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

( new PNO_Registration_Custom_Fields() )->init();
