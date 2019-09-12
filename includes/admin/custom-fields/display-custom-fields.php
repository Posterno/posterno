<?php
/**
 * Displays the content of the custom fields editor page.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the list of js variables passed to vuejs for the custom fields editor.
 *
 * @return array
 */
function pno_get_custom_fields_editor_js_vars() {

	$js_vars = [
		'plugin_url'                     => PNO_PLUGIN_URL,
		'rest'                           => esc_url_raw( rest_url() ),
		'nonce'                          => wp_create_nonce( 'wp_rest' ),
		'create_field_nonce'             => wp_create_nonce( 'wp_rest' ),
		'delete_field_nonce'             => wp_create_nonce( 'wp_rest' ),
		'trashed'                        => isset( $_GET['trashed'] ) ? true : false,
		'field_types'                    => pno_get_registered_field_types(
			[
				'social-profiles',
				'listing-category',
				'listing-tags',
				'term-select',
				'term-multiselect',
				'term-checklist',
				'term-chain-dropdown',
				'listing-opening-hours',
				'listing-location',
			]
		),
		'listing_field_types'            => pno_get_registered_field_types(
			[
				'social-profiles',
				'listing-category',
				'listing-tags',
				'listing-opening-hours',
				'listing-location',
			]
		),
		'roles'                          => pno_get_roles( true ),
		'pages'                          => [
			'selector'     => admin_url( 'edit.php?post_type=listings&page=posterno-custom-fields' ),
			'profile'      => admin_url( 'edit.php?post_type=listings&page=posterno-custom-profile-fields' ),
			'registration' => admin_url( 'edit.php?post_type=listings&page=posterno-custom-registration-form' ),
			'listings'     => admin_url( 'edit.php?post_type=listings&page=posterno-custom-listings-fields' ),
		],
		'import_url'                     => esc_url( admin_url( 'edit.php?post_type=listings&page=listingsfield_importer' ) ),
		'export_url'                     => esc_url( admin_url( 'edit.php?post_type=listings&page=listings_fields_exporter' ) ),
		'import_profiles_fields_url'     => esc_url( admin_url( 'edit.php?post_type=listings&page=profilesfield_importer' ) ),
		'export_profiles_fields_url'     => esc_url( admin_url( 'edit.php?post_type=listings&page=profile_fields_exporter' ) ),
		'import_registration_fields_url' => esc_url( admin_url( 'edit.php?post_type=listings&page=registrationfield_importer' ) ),
		'export_registration_fields_url' => esc_url( admin_url( 'edit.php?post_type=listings&page=registration_fields_exporter' ) ),
		'labels'                         => [
			'documentation'       => esc_html__( 'Documentation', 'posterno' ),
			'addons'              => esc_html__( 'Extensions', 'posterno' ),
			'title'               => esc_html__( 'Posterno custom fields', 'posterno' ),
			'custom_users'        => esc_html__( 'Profile fields', 'posterno' ),
			'custom_listings'     => esc_html__( 'Listings fields', 'posterno' ),
			'custom_registration' => esc_html__( 'Registration form', 'posterno' ),
			'custom_fields'       => esc_html__( 'Customize fields', 'posterno' ),
			'custom_form'         => esc_html__( 'Customize form', 'posterno' ),
			'purchase_extension'  => esc_html__( 'Purchase extension', 'posterno' ),
			'import'              => esc_html__( 'Import', 'posterno' ),
			'export'              => esc_html__( 'Export', 'posterno' ),
			'profile'             => [
				'title'            => esc_html__( 'Posterno profile fields editor', 'posterno' ),
				'add_new'          => esc_html__( 'Add new field', 'posterno' ),
				'field_admin_only' => esc_html__( 'This field is editable only by an administrator.', 'posterno' ),
				'field_order'      => esc_html__( 'Drag and drop the rows below to change the order of the fields.', 'posterno' ),
			],
			'registration'        => [
				'title'   => esc_html__( 'Posterno registration form editor', 'posterno' ),
				'add_new' => esc_html__( 'Add new field', 'posterno' ),
			],
			'listing'             => [
				'title'   => esc_html__( 'Posterno listings fields editor', 'posterno' ),
				'add_new' => esc_html__( 'Add new field', 'posterno' ),
			],
			'table'               => [
				'title'     => esc_html__( 'Field title', 'posterno' ),
				'type'      => esc_html__( 'Type', 'posterno' ),
				'required'  => esc_html__( 'Required', 'posterno' ),
				'default'   => esc_html__( 'Default', 'posterno' ),
				'privacy'   => esc_html__( 'Privacy', 'posterno' ),
				'editable'  => esc_html__( 'Editable', 'posterno' ),
				'actions'   => esc_html__( 'Actions', 'posterno' ),
				'not_found' => esc_html__( 'No fields yet, click the button above to add fields.', 'posterno' ),
				'edit'      => esc_html__( 'Edit', 'posterno' ),
				'role'      => esc_html__( 'User role', 'posterno' ),
				'delete'    => esc_html__( 'Delete', 'posterno' ),
				'roles'     => [
					'all' => esc_html__( 'All user roles', 'posterno' ),
				],
			],
			'modal'               => [
				'field_name'                => esc_html__( 'New field name:', 'posterno' ),
				'field_type'                => esc_html__( 'Select field type:', 'posterno' ),
				'about_to_delete'           => esc_html__( 'You are about to delete the field:', 'posterno' ),
				'delete_message'            => esc_html__( 'Are you sure you want to delete this field? This action is irreversible.', 'posterno' ),
				'field_profile'             => esc_html__( 'Map registration field to profile field:', 'posterno' ),
				'field_profile_description' => esc_html__( 'Registration fields must be mapped to an existing profile field in order to store information related to the field.', 'posterno' ),
				'field_select'              => esc_html__( 'Select a field...', 'posterno' ),
			],
			'success'             => esc_html__( 'Changes successfully saved.', 'posterno' ),
		],
	];

	return $js_vars;

}

/**
 * Function responsible of displaying the custom fields selector page.
 * Actual output handled by vuejs.
 *
 * @since 0.1.0
 * @return void
 */
function pno_custom_fields_page() {
	echo '<div id="posterno-custom-fields-page"></div>';
}

/**
 * Displays the registration form editor page. Actual output handled via Vuejs.
 *
 * @return void
 */
function pno_custom_registration_fields_page() {
	echo '<div id="posterno-registration-form-page"></div>';
}

/**
 * Displays the profile fields editor page. Actual output handled via Vuejs.
 *
 * @return void
 */
function pno_custom_profile_fields_page() {
	echo '<div id="posterno-profile-fields-page"></div>';
}

/**
 * Displays the listings fields editor page. Actual output handled via Vuejs.
 *
 * @return void
 */
function pno_custom_listings_fields_page() {
	echo '<div id="posterno-listings-fields-page"></div>';
}
