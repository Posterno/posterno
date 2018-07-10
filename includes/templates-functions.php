<?php
/**
 * List of functions used within template files.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Parses a form action string to create an ID for the form tag of a form.
 *
 * @param string $form form name.
 * @return void
 */
function pno_get_form_id( $form ) {
	$id = 'pno-form-' . $form;
	return esc_attr( $id );
}

/**
 * Retrieve the correct label for the login form.
 *
 * @return string
 */
function pno_get_login_label() {

	$label = esc_html__( 'Username' );

	$login_method = pno_get_option( 'login_method' );

	if ( $login_method == 'email' ) {
		$label = esc_html__( 'Email' );
	} elseif ( $login_method == 'username_email' ) {
		$label = esc_html__( 'Username or email' );
	}

	return $label;

}

/**
 * Retrieve the classes for a given form field as an array.
 *
 * @param array $field
 * @param string $class
 * @return array
 */
function pno_get_form_field_class( $field_key, $field, $class = '' ) {

	$classes = [ 'pno-field' ];

	if ( $field_key ) {
		$classes[] = 'pno-field-' . $field_key;
	}

	$classes[] = 'pno-field-' . $field['type'];

	if ( $field['type'] == 'checkbox' ) {
		$classes[] = 'form-check';
	} else {
		$classes[] = 'form-group';
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current form field.
	 *
	 * @param array $classes
	 * @param array $field
	 * @param string $class
	 */
	$classes = apply_filters( 'pno_form_field_classes', $classes, $field_key, $field, $class );

	return array_unique( $classes );

}

/**
 * Display the classes for a given form field.
 *
 * @param string $field_key
 * @param array $field
 * @param string $class
 * @return void
 */
function pno_form_field_class( $field_key, $field, $class = '' ) {
	// Separates classes with a single space, collates classes for body element
	echo 'class="' . join( ' ', pno_get_form_field_class( $field_key, $field, $class ) ) . '"';
}

/**
 * Retrieve the url where to redirect the user after login.
 *
 * @return string
 */
function pno_get_login_redirect() {

	$url = home_url();

	$custom_page = pno_get_option( 'login_redirect' );

	if ( is_array( $custom_page ) && isset( $custom_page['value'] ) ) {
		$url = get_permalink( $custom_page['value'] );
	}

	if ( isset( $_GET['redirect_to'] ) && ! empty( $_GET['redirect_to'] ) ) {
		$url = esc_url_raw( $_GET['redirect_to'] );
	}

	/**
	 * Filter the login redirect url. This is the url where users
	 * are redirect after they log into the website through the
	 * posterno's login form.
	 *
	 * @param string $url the url.
	 */
	return apply_filters( 'pno_login_redirect', $url );

}

/**
 * Retrieve the url where to redirect users after they register.
 *
 * @return void
 */
function pno_get_registration_redirect() {

	$url = false;

	$registration_redirect_page = pno_get_option( 'registration_redirect' );

	if ( is_array( $registration_redirect_page ) && isset( $registration_redirect_page['value'] ) ) {
		$url = get_permalink( $registration_redirect_page['value'] );
	}

	if ( isset( $_GET['redirect_to'] ) && ! empty( $_GET['redirect_to'] ) ) {
		$url = esc_url_raw( $_GET['redirect_to'] );
	}

	/**
	 * Filter the registration redirect url. This is the url where users
	 * are redirect after they register into the website through the
	 * posterno's registration form.
	 *
	 * @param string $url the url.
	 */
	return apply_filters( 'pno_registration_redirect', $url );

}

/**
 * Programmatically log a user in given an email address or user id.
 * This function should usually be followed by a redirect.
 *
 * @param mixed $email_or_id
 * @return void
 */
function pno_log_user_in( $email_or_id ) {

	$get_by = 'id';

	if ( is_email( $email_or_id ) ) {
		$get_by = 'email';
	}

	$user     = get_user_by( $get_by, $email_or_id );
	$user_id  = $user->ID;
	$username = $user->user_login;

	wp_set_current_user( $user_id, $username );
	wp_set_auth_cookie( $user_id );
	do_action( 'wp_login', $username, $user );

}

/**
 * Send a registration confirmation email to the user and administrator.
 *
 * @param string $user_id
 * @param string $psw
 * @return void
 */
function pno_send_registration_confirmation_email( $user_id, $psw = false ) {

	if ( ! $user_id ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Bail if no user found.
	if ( ! $user instanceof WP_User ) {
		return;
	}

	// User's email details.
	$subject = pno_get_option( 'registration_confirmation_subject' );
	$message = pno_get_option( 'registration_confirmation_content' );
	$heading = pno_get_option( 'registration_confirmation_heading' );

	// Admin email's details.
	$subject_admin = pno_get_option( 'registration_confirmation_admin_subject' );
	$message_admin = pno_get_option( 'registration_confirmation_admin_content' );

	// Send the email to the site's administrator.
	if ( $subject_admin ) {
		posterno()->emails->__set( 'user_id', $user_id );
		posterno()->emails->send( get_option( 'admin_email' ), $subject_admin, $message_admin );
	}

	// Send the email to the end user only if a subject and content is specified.
	if ( ! $subject || empty( $subject ) || ! $message || empty( $message ) ) {
		return;
	}

	posterno()->emails->__set( 'user_id', $user_id );

	if ( $heading ) {
		posterno()->emails->__set( 'heading', $heading );
	}

	if ( ! empty( $psw ) ) {
		posterno()->emails->__set( 'plain_text_password', $psw );
	}

	posterno()->emails->send( $user->data->user_email, $subject, $message );

}

/**
 * Replace during email parsing characters.
 *
 * @param string $str
 * @return void
 */
function pno_starmid( $str ) {
	switch ( strlen( $str ) ) {
		case 0:
			return false;
		case 1:
			return $str;
		case 2:
			return $str[0] . '*';
		default:
			return $str[0] . str_repeat( '*', strlen( $str ) - 2 ) . substr( $str, -1 );
	}
}

/**
 * Mask an email address.
 *
 * @param string $email_address
 * @return void
 */
function pno_mask_email_address( $email_address ) {
	if ( ! filter_var( $email_address, FILTER_VALIDATE_EMAIL ) ) {
		return false;
	}
	list( $u, $d ) = explode( '@', $email_address );
	$d             = explode( '.', $d );
	$tld           = array_pop( $d );
	$d             = implode( '.', $d );
	return pno_starmid( $u ) . '@' . pno_starmid( $d ) . ".$tld";
}
