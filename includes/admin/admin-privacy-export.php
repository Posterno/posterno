<?php
/**
 * Handles integration with WordPress privacy export tools.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register new exporters for additional personal data used by Posterno.
 *
 * @param array $exporters
 * @return void
 */
function pno_plugin_register_exporters( $exporters ) {

	$exporters[] = array(
		'exporter_friendly_name' => esc_html__( 'Additional account details' ),
		'callback'               => 'pno_export_profile_fields_user_data',
	);

	return $exporters;

}
add_filter( 'wp_privacy_personal_data_exporters', 'pno_plugin_register_exporters' );

/**
 * Export all profile fields data added by Posterno.
 *
 * @param string $email_address
 * @param integer $page
 * @return void
 */
function pno_export_profile_fields_user_data( $email_address, $page = 1 ) {

	$export_items = array();
	$user         = get_user_by( 'email', $email_address );

	if ( $user && $user->ID ) {

		$item_id     = "additional-user-data-{$user->ID}";
		$group_id    = 'user';
		$group_label = esc_html__( 'Additional account details' );
		$data        = array();

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

					if ( ! pno_is_default_profile_field( $profile_field->get_meta() ) || $profile_field->get_meta() == 'avatar' ) {

						$value = $profile_field->get_value();

						if ( $profile_field->get_type() == 'checkbox' ) {
							$value = esc_html__( 'Yes' );
						} elseif ( $profile_field->get_type() == 'multiselect' || $profile_field->get_type() == 'multicheckbox' ) {

							$stored_field_options = $profile_field->get_selectable_options();
							$stored_options       = [];
							$found_options_labels = [];

							foreach ( $stored_field_options as $key => $stored_option ) {
								$stored_options[ $key ] = $stored_option;
							}

							$values = [];

							foreach ( $value as $user_stored_value ) {
								$values[] = $stored_options[ $user_stored_value ];
							}

							$value = implode( ', ', $values );

						}

						$data[] = array(
							'name'  => $profile_field->get_name(),
							'value' => $value,
						);

					}
				}
			}
		}

		$export_items[] = array(
			'group_id'    => $group_id,
			'group_label' => $group_label,
			'item_id'     => $item_id,
			'data'        => $data,
		);

	}

	return array(
		'data' => $export_items,
		'done' => true,
	);

}
