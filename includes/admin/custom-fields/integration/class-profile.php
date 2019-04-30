<?php
/**
 * Handles registration of custom fields for profiles.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Field\Integration;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class that handles settings integration for profiles custom fields.
 */
class Profile {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {

		add_action( 'carbon_fields_register_fields', [ $this, 'register_profile_fields' ] );

	}

	/**
	 * Register profile fields in the admin panel.
	 *
	 * @return void
	 */
	public function register_profile_fields() {

		/*
		$admin_fields = remember_transient(
			'pno_admin_custom_profile_fields',
			function () {

				$profile_fields = new \PNO\Database\Queries\Profile_Fields( [ 'user_meta_key__not_in' => pno_get_registered_default_meta_keys() ] );

				$custom_fields_ids = [];

				foreach ( $profile_fields->items as $field ) {
					$custom_fields_ids[] = $field->get_post_id();
				}

				$admin_fields = [];

				foreach ( $custom_fields_ids as $profile_field_id ) {

					$custom_profile_field = new \PNO\Field\Profile( $profile_field_id );

					if ( $custom_profile_field instanceof \PNO\Field\Profile && ! empty( $custom_profile_field->get_object_meta_key() ) ) {

						$type = $custom_profile_field->get_type();

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
							if ( ! empty( $custom_profile_field->get_options() ) ) {
								$admin_fields[] = Field::make( $type, $custom_profile_field->get_object_meta_key(), $custom_profile_field->get_name() )->add_options( $custom_profile_field->get_options() );
							}
						} elseif ( $type == 'file' ) {
							if ( $custom_profile_field->is_multiple() ) {
								$admin_fields[] = Field::make( 'complex', $custom_profile_field->get_object_meta_key() )->add_fields(
									array(
										Field::make( 'text', 'url', esc_html__( 'File url', 'posterno' ) ),
										Field::make( 'hidden', 'path' ),
									)
								);
							} else {
								$admin_fields[] = Field::make( $type, $custom_profile_field->get_object_meta_key(), $custom_profile_field->get_name() )->set_value_type( 'url' );
							}
						} elseif ( $custom_profile_field->get_type() == 'number' ) {
							$admin_fields[] = Field::make( $type, $custom_profile_field->get_object_meta_key(), $custom_profile_field->get_name() )->set_attribute( 'type', 'number' );
						} elseif ( $custom_profile_field->get_type() == 'password' ) {
							$admin_fields[] = Field::make( $type, $custom_profile_field->get_object_meta_key(), $custom_profile_field->get_name() )->set_attribute( 'type', 'password' );
						} else {
							$admin_fields[] = Field::make( $type, $custom_profile_field->get_object_meta_key(), $custom_profile_field->get_name() );
						}
					}
				}

				return $admin_fields;

			}
		);*/

		if ( ! empty( $admin_fields ) ) {
			Container::make( 'user_meta', esc_html__( 'Additional details', 'posterno' ) )
				->add_fields( [] );
		}

	}

}

( new Profile() )->init();
