<?php
/**
 * Handles integration with WordPress privacy erasure tools.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register a custom eraser for the plugin.
 *
 * @param array $erasers list of erasers.
 * @return array
 */
function pno_plugin_register_erasers( $erasers = array() ) {

	$erasers[] = array(
		'eraser_friendly_name' => esc_html__( 'Additional account details', 'posterno' ),
		'callback'             => 'pno_plugin_profile_data_eraser',
	);

	return $erasers;

}
add_filter( 'wp_privacy_personal_data_erasers', 'pno_plugin_register_erasers' );

/**
 * Erases all the values submitted through profile fields of Posterno.
 *
 * @param string  $email_address email address.
 * @param integer $page page counter.
 * @return array
 */
function pno_plugin_profile_data_eraser( $email_address, $page = 1 ) {

	if ( empty( $email_address ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$user           = get_user_by( 'email', $email_address );
	$messages       = array();
	$items_removed  = false;
	$items_retained = false;

	if ( $user && $user->ID ) {

		$fields_query = new PNO\Database\Queries\Profile_Fields( [ 'number' => 100 ] );

		if ( isset( $fields_query->items ) && is_array( $fields_query->items ) && ! empty( $fields_query->items ) ) {
			foreach ( $fields_query->items as $field ) {

				if ( ! pno_is_default_field( $field->getObjectMetaKey() ) || $field->getObjectMetaKey() == 'avatar' ) {

					$field->loadValue( $user->ID );

					if ( ! empty( $field->getValue() ) ) {
						$meta = $field->getObjectMetaKey();

						if ( $meta === 'avatar' ) {
							$meta = 'current_user_avatar';
						}

						$field_to_remove = delete_user_meta( $user->ID, '_' . $meta );

						if ( $field_to_remove ) {
							$items_removed = true;
						} else {
							$messages[]     = sprintf( esc_html__( 'Your "%s" was unable to be removed at this time.', 'posterno' ), $field->getTitle() );
							$items_retained = true;
						}
					}
				}
			}
		}
	}

	return array(
		'items_removed'  => $items_removed,
		'items_retained' => $items_retained,
		'messages'       => $messages,
		'done'           => true,
	);

}
