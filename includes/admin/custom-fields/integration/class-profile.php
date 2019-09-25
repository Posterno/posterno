<?php
/**
 * Handles registration of custom fields for profiles.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
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

		$admin_fields = remember_transient(
			'pno_admin_custom_profile_fields',
			function () {

				$profile_fields = new \PNO\Database\Queries\Profile_Fields( [ 'user_meta_key__not_in' => pno_get_registered_default_meta_keys() ] );

				$admin_fields = [];

				if ( isset( $profile_fields->items ) && is_array( $profile_fields->items ) && ! empty( $profile_fields->items ) ) {
					foreach ( $profile_fields->items as $custom_profile_field ) {

						if ( $custom_profile_field instanceof \PNO\Entities\Field\Profile && ! empty( $custom_profile_field->getObjectMetaKey() ) ) {

							$type = $custom_profile_field->getType();

							if ( $type === 'heading' ) {
								continue;
							}

							switch ( $type ) {
								case 'url':
								case 'email':
								case 'number':
								case 'password':
								case 'pricing':
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
								if ( ! empty( $custom_profile_field->getOptions() ) ) {
									$admin_fields[] = Field::make( $type, $custom_profile_field->getObjectMetaKey(), $custom_profile_field->getTitle() )->add_options( $custom_profile_field->getOptions() );
								}
							} elseif ( $type == 'file' ) {
								if ( $custom_profile_field->isMultiple() ) {
									$admin_fields[] = Field::make( 'complex', $custom_profile_field->getObjectMetaKey() )->add_fields(
										array(
											Field::make( 'text', 'url', esc_html__( 'File url', 'posterno' ) ),
											Field::make( 'hidden', 'path' ),
										)
									);
								} else {
									$admin_fields[] = Field::make( $type, $custom_profile_field->getObjectMetaKey(), $custom_profile_field->getTitle() )->set_value_type( 'url' );
								}
							} elseif ( $custom_profile_field->getType() == 'number' ) {
								$admin_fields[] = Field::make( $type, $custom_profile_field->getObjectMetaKey(), $custom_profile_field->getTitle() )->set_attribute( 'type', 'number' );
							} elseif ( $custom_profile_field->getType() == 'password' ) {
								$admin_fields[] = Field::make( $type, $custom_profile_field->getObjectMetaKey(), $custom_profile_field->getTitle() )->set_attribute( 'type', 'password' );
							} else {
								$admin_fields[] = Field::make( $type, $custom_profile_field->getObjectMetaKey(), $custom_profile_field->getTitle() );
							}
						}
					}
				}

				return $admin_fields;

			}
		);

		if ( ! empty( $admin_fields ) ) {
			Container::make( 'user_meta', esc_html__( 'Additional details', 'posterno' ) )
				->add_fields( $admin_fields );
		}

	}

}

( new Profile() )->init();
