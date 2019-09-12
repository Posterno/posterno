<?php
/**
 * Handles integration with WordPress privacy export tools.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register new exporters for additional personal data used by Posterno.
 *
 * @param array $exporters registered exporters.
 * @return array
 */
function pno_plugin_register_exporters( $exporters ) {

	$exporters[] = array(
		'exporter_friendly_name' => esc_html__( 'Additional account details', 'posterno' ),
		'callback'               => 'pno_export_profile_fields_user_data',
	);

	return $exporters;

}
add_filter( 'wp_privacy_personal_data_exporters', 'pno_plugin_register_exporters' );

/**
 * Export all profile fields data added by Posterno.
 *
 * @param string  $email_address email address of the user.
 * @param integer $page page list.
 * @return array
 */
function pno_export_profile_fields_user_data( $email_address, $page = 1 ) {

	$export_items = array();
	$user         = get_user_by( 'email', $email_address );

	if ( $user && $user->ID ) {

		$item_id     = "additional-user-data-{$user->ID}";
		$group_id    = 'user';
		$group_label = esc_html__( 'Additional account details', 'posterno' );
		$data        = array();

		$fields_query = new PNO\Database\Queries\Profile_Fields( [ 'number' => 100 ] );

		if ( isset( $fields_query->items ) && is_array( $fields_query->items ) && ! empty( $fields_query->items ) ) {

			foreach ( $fields_query->items as $field ) {

				if ( ! pno_is_default_field( $field->getObjectMetaKey() ) || $field->getObjectMetaKey() == 'avatar' ) {

					$field->loadValue( $user->ID );

					$value = $field->getValue();

					if ( $field->getType() == 'checkbox' ) {
						$value = esc_html__( 'Yes', 'posterno' );
					} elseif ( $field->getType() == 'multiselect' || $field->getType() == 'multicheckbox' ) {

						$stored_field_options = $field->getOptions();
						$stored_options       = [];

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
						'name'  => $field->getTitle(),
						'value' => $value,
					);

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
