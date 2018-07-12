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
				'label'       => false,
				'type'        => 'checkbox',
				'description' => apply_filters( 'pno_terms_text', sprintf( __( 'By registering to this website you agree to the <a href="%s" target="_blank">terms &amp; conditions</a>.' ), get_permalink( $terms_page ) ) ),
				'required'    => true,
				'priority'    => 101,
			);
		}
	}

	if ( get_option( 'wp_page_for_privacy_policy' ) ) {
		$fields['registration']['privacy'] = array(
			'label'       => false,
			'type'        => 'checkbox',
			'description' => apply_filters( 'wpum_privacy_text', sprintf( __( 'I have read and accept the <a href="%1$s" target="_blank">privacy policy</a> and allow "%2$s" to collect and store the data I submit through this form.' ), get_permalink( get_option( 'wp_page_for_privacy_policy' ) ), get_bloginfo( 'name' ) ) ),
			'required'    => true,
			'priority'    => 102,
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
