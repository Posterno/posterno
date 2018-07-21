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
	$classes[] = 'form-group';
	if ( $field['type'] == 'checkbox' ) {
		$classes[] = 'form-check';
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
 * Retrieve a list of allowed users role on the registration page
 *
 * @since 1.0.0
 * @return array $roles An array of the roles
 */
function pno_get_allowed_user_roles() {
	global $wp_roles;
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}
	$user_roles         = array();
	$selected_roles     = pno_get_option( 'allowed_roles' );
	$allowed_user_roles = is_array( $selected_roles ) ? $selected_roles : array( $selected_roles );
	foreach ( $allowed_user_roles as $role ) {
		$user_roles[ $role['value'] ] = $wp_roles->roles[ $role['value'] ]['name'];
	}
	return $user_roles;
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

/**
 * Sort an array by the priority key value.
 *
 * @param array $a
 * @param array $b
 * @return void
 */
function pno_sort_array_by_priority( $a, $b ) {
	if ( $a['priority'] == $b['priority'] ) {
		return 0;
	}
	return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
}

/**
 * Retrieve the url of a given dashboard navigation item.
 *
 * @param string $item
 * @return void
 */
function pno_get_dashboard_navigation_item_url( $key, $item = [] ) {

	$base_url = rtrim( get_permalink( pno_get_dashboard_page_id() ), '/' );

	if ( $key == 'logout' ) {
		$base_url = wp_logout_url();
	} elseif ( isset( $item['is_first'] ) ) {
		$base_url = $base_url;
	} else {
		$base_url = $base_url . '/' . $key;
	}

	return apply_filters( 'pno_dashboard_navigation_item_url', $base_url, $key, $item );

}

/**
 * Retrieve the classes for a given dashboard navigation item as an array.
 *
 * @param string $key
 * @param array $item
 * @param string $class
 * @return array
 */
function pno_get_dashboard_navigation_item_class( $key, $item, $class = '' ) {

	$classes   = [ 'pno-dashboard-item' ];
	$classes[] = 'list-group-item';
	$classes[] = 'list-group-item-action';

	if ( $key ) {
		$classes[] = 'item-' . $key;
	}

	// Determine the currently active tab:
	if ( pno_is_dashboard_navigation_item_active( $key ) ) {
		$classes[] = 'active';
	} elseif ( empty( get_query_var( 'dashboard_navigation_item' ) ) && isset( $item['is_first'] ) ) {
		$classes[] = 'active';
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current navigation item.
	 *
	 * @param array $classes
	 * @param array $field
	 * @param string $class
	 */
	$classes = apply_filters( 'pno_dashboard_navigation_item_classes', $classes, $key, $item, $class );

	return array_unique( $classes );

}

/**
 * Display the classes for a given dashboard navigation item.
 *
 * @param string $key
 * @param array $item
 * @param string $class
 * @return void
 */
function pno_dashboard_navigation_item_class( $key, $item, $class = '' ) {
	// Separates classes with a single space, collates classes for body element.
	echo 'class="' . join( ' ', pno_get_dashboard_navigation_item_class( $key, $item, $class ) ) . '"';
}

/**
 * Determine if a given navigation item is currently active.
 *
 * @param string $current
 * @return boolean
 */
function pno_is_dashboard_navigation_item_active( $current ) {

	$active = ! empty( get_query_var( 'dashboard_navigation_item' ) ) && get_query_var( 'dashboard_navigation_item' ) == $current ? true : false;

	return $active;

}

/**
 * Retrieve the full hierarchy of a given page or post.
 *
 * @param int $page_id
 * @return void
 */
function pno_get_full_page_hierarchy( $page_id ) {
	$page = get_post( $page_id );
	if ( empty( $page ) || is_wp_error( $page ) ) {
		return [];
	}
	$return         = [];
	$page_obj       = [];
	$page_obj['id'] = $page_id;
	$return[]       = $page_obj;
	if ( $page->post_parent > 0 ) {
		$return = array_merge( $return, pno_get_full_page_hierarchy( $page->post_parent ) );
	}
	return $return;
}

/**
 * Wrapper function for size_format - checks the max size for file fields.
 *
 * @param array   $field
 * @param string  $size  in bytes
 * @return string
 */
function pno_max_upload_size( $field_name = '', $custom_size = false ) {
	// Default max upload size.
	$output = size_format( wp_max_upload_size() );

	if ( $custom_size ) {
		$output = size_format( intval( $custom_size ), 0 );
	}
	return $output;
}

/**
 * Determine default profile fields.
 *
 * @param string $key
 * @return boolean
 */
function pno_is_default_profile_field( $key ) {

	if ( ! $key ) {
		return;
	}

	$default = false;

	switch ( $key ) {
		case 'avatar':
		case 'first_name':
		case 'last_name':
		case 'email':
		case 'website':
		case 'description':
			$default = true;
			break;
	}

	return apply_filters( 'pno_is_default_field', (bool) $default );

}

/**
 * Retrieve the list of registered field types and their labels.
 *
 * @return array
 */
function pno_get_registered_field_types() {

	$types = [
		'text'          => esc_html__( 'Single text line' ),
		'textarea'      => esc_html__( 'Textarea' ),
		'checkbox'      => esc_html__( 'Checkbox' ),
		'email'         => esc_html__( 'Email address' ),
		'password'      => esc_html__( 'Password' ),
		'url'           => esc_html__( 'Website' ),
		'select'        => esc_html__( 'Dropdown' ),
		'radio'         => esc_html__( 'Radio' ),
		'number'        => esc_html__( 'Number' ),
		'multiselect'   => esc_html__( 'Multiselect' ),
		'multicheckbox' => esc_html__( 'Multiple checkboxes' ),
		'file'          => esc_html__( 'File' ),
		'datepicker'    => esc_html__( 'Date picker' ),
	];

	/**
	 * Allows developers to register a new field type.
	 *
	 * @since 0.1.0
	 * @param array $types all registered field types.
	 */
	$types = apply_filters( 'pno_registered_field_types', $types );

	asort( $types );

	return $types;

}
