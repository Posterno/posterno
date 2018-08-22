<?php
/**
 * List of functions used within template files.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Parses a form action string to create an ID for the form tag of a form.
 *
 * @param string $form form name.
 * @return string
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

	if ( $login_method === 'email' ) {
		$label = esc_html__( 'Email' );
	} elseif ( $login_method === 'username_email' ) {
		$label = esc_html__( 'Username or email' );
	}

	return $label;

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
 * @return string
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
 * @param mixed $email_or_id either the email address or the id of the user.
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
 * @param string $user_id id number of the user.
 * @param string $psw optional custom password.
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
		$wp_roles = new WP_Roles(); // phpcs:ignore
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
 * @param string $str the string to manipulate.
 * @return string
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
 * @param string $email_address email address to mask.
 * @return string
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
 * @param array $a first set.
 * @param array $b second set.
 * @return mixed
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
 * @param string $key item key.
 * @param array  $item item definition.
 * @return string
 */
function pno_get_dashboard_navigation_item_url( $key, $item = [] ) {

	$base_url = rtrim( get_permalink( pno_get_dashboard_page_id() ), '/' );

	// We use information stored in the CSS class to determine what kind of
	// menu item this is, and how it should be treated.
	if ( isset( $item->classes ) && ! empty( $item->classes ) ) {
		$menu_classes = $item->classes;
		if ( is_array( $menu_classes ) ) {
			$menu_classes = implode( ' ', $item->classes );
		}

		preg_match( '/\spno-(.*)-nav/', $menu_classes, $matches );

		if ( ! empty( $matches[1] ) && $matches[1] === 'dashboard' ) {
			return $base_url;
		}
	}

	$base_url = $base_url . '/' . $key;

	return apply_filters( 'pno_dashboard_navigation_item_url', $base_url, $key, $item );

}

/**
 * Retrieve the classes for a given dashboard navigation item as an array.
 *
 * @param string $key item key.
 * @param array  $item item definition.
 * @param string $class optional additional class.
 * @return array
 */
function pno_get_dashboard_navigation_item_class( $key, $item, $class = '' ) {

	$classes   = [ 'pno-dashboard-item' ];
	$classes[] = 'list-group-item';
	$classes[] = 'list-group-item-action';

	if ( $key ) {
		$classes[] = 'item-' . $key;
	}

	// Determine the currently active tab.
	if ( pno_is_dashboard_navigation_item_active( $item->pno_identifier ) ) {
		$classes[] = 'active';
	} elseif ( empty( get_query_var( 'dashboard_navigation_item' ) ) && isset( $item->pno_identifier ) && $item->pno_identifier === 'dashboard' ) {
		$classes[] = 'active';
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current navigation item.
	 *
	 * @param array $classes list of classes.
	 * @param string $key item key.
	 * @param array $item item definition.
	 * @param string $class optional class.
	 */
	$classes = apply_filters( 'pno_dashboard_navigation_item_classes', $classes, $key, $item, $class );

	return array_unique( $classes );

}

/**
 * Display the classes for a given dashboard navigation item.
 *
 * @param string $key item key.
 * @param object $item item definition.
 * @param string $class optional class.
 * @return void
 */
function pno_dashboard_navigation_item_class( $key, $item, $class = '' ) {
	// Separates classes with a single space, collates classes for body element.
	// phpcs:ignore
	echo 'class="' . join( ' ', pno_get_dashboard_navigation_item_class( $key, $item, $class ) ) . '"';
}

/**
 * Determine if a given navigation item is currently active.
 *
 * @param string $current key of the item to check.
 * @return boolean
 */
function pno_is_dashboard_navigation_item_active( $current ) {

	$active = ! empty( get_query_var( 'dashboard_navigation_item' ) ) && get_query_var( 'dashboard_navigation_item' ) == $current ? true : false;

	return $active;

}

/**
 * Retrieve the full hierarchy of a given page or post.
 *
 * @param int $page_id page id number.
 * @return mixed
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
 * Get nav menu items by location.
 *
 * @param string $location the menu location to check.
 * @param array  $args optional settings.
 * @return mixed
 */
function pno_get_nav_menu_items_by_location( $location, $args = [] ) {

	$locations  = get_nav_menu_locations();
	$object     = wp_get_nav_menu_object( $locations[ $location ] );
	$menu_items = wp_get_nav_menu_items( $object->name, $args );

	return $menu_items;
}

/**
 * Retrieve the classes for a given uploaded media file within a form.
 *
 * @param string $key field key.
 * @param array  $item field definition.
 * @param string $class optional additional class.
 * @return array
 */
function pno_get_uploaded_file_field_media_class( $key, $item, $class = '' ) {

	$classes = [ 'pno-uploaded-media' ];

	if ( $key ) {
		$classes[] = 'pno-media-' . $key;
	}

	if ( $key == 'avatar' ) {
		$classes[] = 'rounded-circle';
		$classes[] = 'border';
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current uploaded media file item.
	 *
	 * @param array $classes list of classes.
	 * @param string $key field key.
	 * @param array $field field definition.
	 * @param string $class optional class.
	 */
	$classes = apply_filters( 'pno_uploaded_file_field_media_classes', $classes, $key, $item, $class );

	return array_unique( $classes );

}

/**
 * Display the classes for a given file field uploaded image.
 *
 * @param string $key field key.
 * @param object $item field definition.
 * @param string $class optional class.
 * @return void
 */
function pno_uploaded_file_field_media_class( $key, $item, $class = '' ) {
	// Separates classes with a single space, collates classes for the element.
	// phpcs:ignore
	echo 'class="' . join(' ', pno_get_uploaded_file_field_media_class( $key, $item, $class ) ) . '"';
}
