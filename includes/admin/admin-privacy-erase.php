<?php
/**
 * Handles integration with WordPress privacy erasure tools.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
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
		'eraser_friendly_name' => esc_html__( 'Additional account details' ),
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

		$fields_query_args = [
			'post_type'              => 'pno_users_fields',
			'posts_per_page'         => 100,
			'nopaging'               => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'post_status'            => 'publish',
			'fields'                 => 'ids',
		];

		$fields_query = new WP_Query( $fields_query_args );

		if ( is_array( $fields_query->get_posts() ) && ! empty( $fields_query->get_posts() ) ) {

			foreach ( $fields_query->get_posts() as $field_id ) {

				$profile_field = new PNO_Profile_Field( $field_id, $user->ID );

				if ( $profile_field instanceof PNO_Profile_Field && $profile_field->get_id() > 0 ) {

					if ( ! pno_is_default_field( $profile_field->get_meta() ) || $profile_field->get_meta() == 'avatar' ) {

						if ( ! empty( $profile_field->get_value() ) ) {

							$meta = $profile_field->get_meta();

							if ( $meta == 'avatar' ) {
								$meta = 'current_user_avatar';
							}

							$field_to_remove = delete_user_meta( $user->ID, '_' . $meta );

							if ( $field_to_remove ) {
								$items_removed = true;
							} else {
								$messages[]     = sprintf( esc_html__( 'Your "%s" was unable to be removed at this time.' ), $profile_field->get_name() );
								$items_retained = true;
							}
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
