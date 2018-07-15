<?php
/**
 * List of functions used all around within the plugin.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the ID number of the selected login page.
 *
 * @return mixed
 */
function pno_get_login_page_id() {

	$login_page  = false;
	$page_option = pno_get_option( 'login_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$login_page = absint( $page_option['value'] );
	}

	return $login_page;

}

/**
 * Retrieve the ID number of the selected registration page.
 *
 * @return mixed
 */
function pno_get_registration_page_id() {

	$registration_page = false;
	$page_option       = pno_get_option( 'registration_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$registration_page = absint( $page_option['value'] );
	}

	return $registration_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_password_recovery_page_id() {

	$password_page = false;
	$page_option   = pno_get_option( 'password_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$password_page = absint( $page_option['value'] );
	}

	return $password_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_dashboard_page_id() {

	$dashboard_page = false;
	$page_option    = pno_get_option( 'dashboard_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$dashboard_page = absint( $page_option['value'] );
	}

	return $dashboard_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_profile_page_id() {

	$profile_page = false;
	$page_option  = pno_get_option( 'profile_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$profile_page = absint( $page_option['value'] );
	}

	return $profile_page;

}

/**
 * Retrieve the list of registration form fields.
 *
 * @return void
 */
function pno_get_registration_fields() {

	$fields = array(
		'registration' => array(
			'username' => array(
				'label'       => esc_html__( 'Username' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => '',
				'priority'    => 1,
			),
			'email'    => array(
				'label'       => __( 'Email address' ),
				'type'        => 'email',
				'required'    => true,
				'placeholder' => '',
				'priority'    => 2,
			),
			'password' => array(
				'label'    => __( 'Password' ),
				'type'     => 'password',
				'required' => true,
				'priority' => 3,
			),
		),
	);

	if ( pno_get_option( 'enable_role_selection' ) ) {
		$fields['registration']['role'] = array(
			'label'    => __( 'Register as:' ),
			'type'     => 'select',
			'required' => true,
			'options'  => pno_get_allowed_user_roles(),
			'priority' => 99,
			'value'    => get_option( 'default_role' ),
		);
	}

	$fields['registration']['robo'] = [
		'label'    => esc_html__( 'If you\'re human leave this blank:' ),
		'type'     => 'text',
		'required' => false,
		'priority' => 100,
	];

	// Add a terms field is enabled.
	if ( pno_get_option( 'enable_terms' ) ) {
		$terms_page = pno_get_option( 'terms_page' );
		$terms_page = is_array( $terms_page ) && isset( $terms_page['value'] ) ? $terms_page['value'] : false;
		if ( $terms_page ) {
			$fields['registration']['terms'] = array(
				'label'    => apply_filters( 'pno_terms_text', sprintf( __( 'By registering to this website you agree to the <a href="%s" target="_blank">terms &amp; conditions</a>.' ), get_permalink( $terms_page ) ) ),
				'type'     => 'checkbox',
				'required' => true,
				'priority' => 101,
			);
		}
	}

	if ( get_option( 'wp_page_for_privacy_policy' ) ) {
		$fields['registration']['privacy'] = array(
			'label'    => apply_filters( 'wpum_privacy_text', sprintf( __( 'I have read and accept the <a href="%1$s" target="_blank">privacy policy</a> and allow "%2$s" to collect and store the data I submit through this form.' ), get_permalink( get_option( 'wp_page_for_privacy_policy' ) ), get_bloginfo( 'name' ) ) ),
			'type'     => 'checkbox',
			'required' => true,
			'priority' => 102,
		);
	}

	/**
	 * Allows developers to register or deregister fields for the registration form.
	 *
	 * @since 0.1.0
	 * @param array $fields array containing the list of fields for the registration form.
	 */
	return apply_filters( 'pno_registration_fields', $fields );

}

/**
 * Defines the list of the fields for the account form.
 * If a user id is passed through the function,
 * the related user's value is loaded within the field.
 *
 * @param string $user_id
 * @return void
 */
function pno_get_account_fields( $user_id = false ) {

	$fields = [
		'first_name'   => [
			'label'       => esc_html__( 'First name' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 1,
		],
		'last_name'   => [
			'label'       => esc_html__( 'Last name' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 2,
		],
		'email'   => [
			'label'       => esc_html__( 'Email address' ),
			'type'        => 'email',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 3,
		],
		'website'   => [
			'label'       => esc_html__( 'Website' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => '',
			'priority'    => 4,
		],
		'description'   => [
			'label'       => esc_html__( 'About me' ),
			'type'        => 'textarea',
			'required'    => false,
			'placeholder' => '',
			'priority'    => 5,
		],
	];

	// Load user's related values within the fields.
	if ( $user_id ) {

		$user = get_user_by( 'id', $user_id );

		if ( $user instanceof WP_User ) {
			foreach ( $fields as $key => $field ) {
				$value = false;
				switch ( $key ) {
					case 'email':
						$value = esc_attr( $user->user_email );
						break;
					case 'website':
						$value = esc_url( $user->user_url );
						break;
					default:
						$value = esc_html( get_user_meta( $user_id, $key, true ) );
						break;
				}
				if ( $value ) {
					$fields[ $key ]['value'] = $value;
				}
			}
		}
	}

	/**
	 * Allows developers to register or deregister custom fields within the
	 * user's account editing form.
	 *
	 * @param array $fields
	 * @param mixed $user_id
	 */
	return apply_filters( 'pno_account_fields', $fields, $user_id );

}

/**
 * Defines a list of navigation items for the dashboard page.
 *
 * @return array
 */
function pno_get_dashboard_navigation_items() {

	$items = [
		'dashboard'    => [
			'name'     => esc_html__( 'Dashboard' ),
			'priority' => 0,
		],
		'edit-account' => [
			'name'     => esc_html__( 'Account details' ),
			'priority' => 1,
		],
		'password' => [
			'name'     => esc_html__( 'Change password' ),
			'priority' => 2,
		],
		'privacy' => [
			'name'     => esc_html__( 'Privacy settings' ),
			'priority' => 3,
		],
		'logout'       => [
			'name'     => esc_html__( 'Logout' ),
			'priority' => 13,
		],
	];

	/**
	 * Allows developers to register or deregister navigation items
	 * for the dashboard menu.
	 *
	 * @param array $items
	 */
	$items = apply_filters( 'pno_dashboard_navigation_items', $items );

	uasort( $items, 'pno_sort_array_by_priority' );

	$first                       = key( $items );
	$items[ $first ]['is_first'] = true;

	return $items;

}
