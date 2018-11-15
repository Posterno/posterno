<?php
/**
 * Handles integration of custom fields for user profiles.
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
 * Class that handles settings registration for profiles custom fields and
 * integration of those fields within WordPress users profiles.
 */
class PNO_Profiles_Custom_Fields {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'carbon_fields_register_fields', [ __class__, 'register_settings' ] );
		add_action( 'carbon_fields_register_fields', [ __class__, 'register_profile_fields' ] );

	}

	/**
	 * Retrieve the list of settings tabs for profile fields settings.
	 *
	 * @return array
	 */
	public static function get_settings_tabs() {

		$tabs = [
			'general'     => esc_html__( 'General' ),
			'validation'  => esc_html__( 'Validation' ),
			'permissions' => esc_html__( 'Permissions' ),
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
	public static function get_general_settings() {

		$settings = [];

		$settings[] = Field::make( 'hidden', 'profile_field_priority' );
		$settings[] = Field::make( 'hidden', 'profile_is_default_field' );

		$settings[] = Field::make( 'select', 'profile_field_type', esc_html__( 'Field type' ) )
			->set_required()
			->add_options( pno_get_registered_field_types() )
			->set_help_text( esc_html__( 'The selected field type determines how the field will look onto the account and registration forms.' ) );

		$settings[] = Field::make( 'complex', 'profile_field_selectable_options', esc_html__( 'Field selectable options' ) )
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
			->set_help_text( esc_html__( 'Add options for this field type.' ) )
			->add_fields(
				array(
					Field::make( 'text', 'option_title', esc_html__( 'Option title' ) )->set_help_text( esc_html__( 'Enter the title of this option.' ) ),
				)
			);

		$settings[] = Field::make( 'text', 'profile_field_label', esc_html__( 'Custom form label' ) )
			->set_help_text( esc_html__( 'This text will be used as label within the registration and account settings forms. Leave blank to use the field title.' ) );

		$settings[] = Field::make( 'text', 'profile_field_placeholder', esc_html__( 'Placeholder' ) )
			->set_help_text( esc_html__( 'This text will appear within the field when empty. Leave blank if not needed.' ) );

		$settings[] = Field::make( 'textarea', 'profile_field_description', esc_html__( 'Field description' ) )
			->set_help_text( esc_html__( 'This is the text that appears as a description within the forms. Leave blank if not needed.' ) );

		$settings[] = Field::make( 'text', 'profile_field_file_max_size', esc_html__( 'Upload max size:' ) )
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
			->set_help_text( esc_html__( 'Enter the maximum file size (in bytes) allowed for uploads through this field. Leave blank to use server settings.' ) );

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
	public static function get_validation_settings() {
		$settings = [];

		$settings[] = Field::make( 'checkbox', 'profile_field_is_required', esc_html__( 'Set as required' ) )
			->set_help_text( esc_html__( 'Enable this option so the field must be filled before the form can be processed.' ) );

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
	public static function get_permissions_settings() {
		$settings = [];

		$settings[] = Field::make( 'checkbox', 'profile_field_is_hidden', esc_html__( 'Admin only?' ) )
			->set_help_text( esc_html__( 'Enable this option to allow only administrators to customize the field. Hidden fields will not be customizable from the account settings page.' ) );

		$settings[] = Field::make( 'checkbox', 'profile_field_is_read_only', esc_html__( 'Set as read only' ) )
			->set_help_text( esc_html__( 'Enable to prevent users from editing this field but still make it visible within the account settings page.' ) );

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
	public static function register_settings() {

		$container = Container::make( 'post_meta', esc_html__( 'Field settings' ) )
			->set_datastore( new PNO\Datastores\DataCompressor() )
			->where( 'post_type', '=', 'pno_users_fields' );

		foreach ( self::get_settings_tabs() as $key => $tab ) {

			$fields = [];

			switch ( $key ) {
				case 'general':
				case 'validation':
				case 'permissions':
					$fields = self::{"get_{$key}_settings"}();
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

		Container::make( 'post_meta', esc_html__( 'Advanced' ) )
			->where( 'post_type', '=', 'pno_users_fields' )
			->set_context( 'side' )
			->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'text', 'profile_field_meta_key', esc_html__( 'Unique meta key' ) )
						->set_required( true )
						->set_help_text( esc_html__( 'The key must be unique for each field and written in lowercase with an underscore ( _ ) separating words e.g country_list or job_title. This will be used to store information about your users into the database of your website.' ) ),
					Field::make( 'text', 'profile_field_custom_classes', esc_html__( 'Custom css classes' ) )
						->set_datastore( new PNO\Datastores\DataCompressor() )
						->set_help_text( esc_html__( 'Enter custom css classes to customize the style of the field. Leave blank if not needed.' ) ),
				)
			);

	}

	/**
	 * Register profile fields in the admin panel.
	 *
	 * @return void
	 */
	public static function register_profile_fields() {

		$extra_account_fields = remember_transient( 'pno_extra_admin_account_fields', function () {
			$fields_query = [
				'post_type'              => 'pno_users_fields',
				'posts_per_page'         => 100,
				'nopaging'               => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'fields'                 => 'ids',
				'post_status'            => 'publish',
				'meta_query'             => array(
					array(
						'key'     => '_profile_field_meta_key',
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
					$custom_field = new PNO_Profile_Field( $field_id );

					if ( $custom_field instanceof PNO_Profile_Field && ! empty( $custom_field->get_meta() ) ) {

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
			}

			return $admin_fields;
		} );

		wp_reset_postdata();

		if ( ! empty( $extra_account_fields ) ) {
			Container::make( 'user_meta', esc_html__( 'Additional details' ) )
				->add_fields( $extra_account_fields );
		}

	}

}

( new PNO_Profiles_Custom_Fields() )->init();
