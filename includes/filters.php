<?php
/**
 * List of filters that should only trigger on the frontend.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Validate authentication with the selected login method.
 *
 * @param object $user the user object.
 * @param string $username the username.
 * @param string $password the password.
 * @return mixed
 */
function pno_authentication( $user, $username, $password ) {

	$authentication_method = pno_get_option( 'login_method' );

	if ( $authentication_method === 'username' ) {

		if ( is_email( $username ) ) {
			return new WP_Error( 'username_only', __( 'Invalid username or incorrect password.' ) );
		}
		return wp_authenticate_username_password( null, $username, $password );

	} elseif ( $authentication_method === 'email' ) {

		if ( ! empty( $username ) && is_email( $username ) ) {

			$user = get_user_by( 'email', $username );

			if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status ) {
				$username = $user->user_login;
				return wp_authenticate_username_password( null, $username, $password );
			}
		} else {
			return new WP_Error( 'email_only', __( 'Invalid email address or incorrect password.' ) );
		}
	}

	return $user;

}
add_filter( 'authenticate', 'pno_authentication', 20, 3 );

/**
 * Filter the wp_login_url function by using the built-in pno's page.
 *
 * @param string  $login_url original login url.
 * @param string  $redirect option redirect.
 * @param boolean $force_reauth reauth.
 * @return string
 */
function pno_login_url( $login_url, $redirect, $force_reauth ) {
	$pno_login_page = pno_get_login_page_id();
	$pno_login_page = get_permalink( $pno_login_page );
	if ( $redirect ) {
		$pno_login_page = add_query_arg( [ 'redirect_to' => rawurlencode( $redirect ) ], $pno_login_page );
	}
	return $pno_login_page;
}

if ( pno_get_option( 'redirect_wp_login' ) ) {
	add_filter( 'login_url', 'pno_login_url', 10, 3 );
}

/**
 * Modify the url retrieved with wp_registration_url().
 *
 * @param string $url original url.
 * @return string
 */
function pno_set_registration_url( $url ) {
	$registration_page = pno_get_registration_page_id();
	if ( $registration_page ) {
		return esc_url( get_permalink( $registration_page ) );
	} else {
		return $url;
	}
}
add_filter( 'register_url', 'pno_set_registration_url' );

/**
 * Modify the url of the wp_lostpassword_url() function.
 *
 * @param string $url original url.
 * @param string $redirect optional redirect.
 * @return string
 */
function pno_set_lostpassword_url( $url, $redirect ) {
	$password_page = pno_get_password_recovery_page_id();
	if ( $password_page ) {
		return esc_url( get_permalink( $password_page ) );
	} else {
		return $url;
	}
}
add_filter( 'lostpassword_url', 'pno_set_lostpassword_url', 10, 2 );

/**
 * Modify the logout url to include redirects set by PNO - if any.
 *
 * @param string $logout_url original url.
 * @param string $redirect optional redirect.
 * @return string
 */
function pno_set_logout_url( $logout_url, $redirect ) {
	$logout_redirect = pno_get_option( 'logout_redirect' );
	if ( ! empty( $logout_redirect ) && is_array( $logout_redirect ) && isset( $logout_redirect['value'] ) && ! $redirect ) {
		$logout_redirect = get_permalink( $logout_redirect['value'] );
		$args            = [
			'action'      => 'logout',
			'redirect_to' => rawurlencode( $logout_redirect ),
		];
		$logout_url      = add_query_arg( $args, site_url( 'wp-login.php', 'login' ) );
		$logout_url      = wp_nonce_url( $logout_url, 'log-out' );
	}
	return $logout_url;
}
add_filter( 'logout_url', 'pno_set_logout_url', 20, 2 );

/**
 * Filters the upload dir when $pno_upload is true.
 *
 * @since 0.1.0
 * @param  array $pathdata path settings.
 * @return array
 */
function pno_upload_dir( $pathdata ) {

	global $pno_upload, $pno_uploading_file;

	if ( ! empty( $pno_upload ) ) {
		$dir = untrailingslashit( apply_filters( 'pno_upload_dir', 'pno-uploads/' . sanitize_key( $pno_uploading_file ), sanitize_key( $pno_uploading_file ) ) );
		if ( empty( $pathdata['subdir'] ) ) {
			$pathdata['path']   = $pathdata['path'] . '/' . $dir;
			$pathdata['url']    = $pathdata['url'] . '/' . $dir;
			$pathdata['subdir'] = '/' . $dir;
		} else {
			$new_subdir         = '/' . $dir . $pathdata['subdir'];
			$pathdata['path']   = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );
			$pathdata['url']    = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );
			$pathdata['subdir'] = $new_subdir;
		}
	}
	return $pathdata;
}
add_filter( 'upload_dir', 'pno_upload_dir' );

/**
 * Properly setup visibility and urls of Posterno's own urls through the menu editor.
 *
 * @param object $menu_item the menu item object.
 * @return object
 */
function pno_setup_nav_menu_item( $menu_item ) {

	if ( is_admin() ) {
		return $menu_item;
	}

	// Prevent a notice error when using the customizer.
	$menu_classes = $menu_item->classes;

	if ( is_array( $menu_classes ) ) {
		$menu_classes = implode( ' ', $menu_item->classes );
	}

	// We use information stored in the CSS class to determine what kind of
	// menu item this is, and how it should be treated.
	preg_match( '/\spno-(.*)-nav/', $menu_classes, $matches );

	// If this isn't a PNO menu item, we can stop here.
	if ( empty( $matches[1] ) ) {
		return $menu_item;
	}

	switch ( $matches[1] ) {
		case 'login':
			if ( is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				$menu_item->url = get_permalink( pno_get_login_page_id() );
			}
			break;
		case 'lost-password':
			if ( is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				$menu_item->url = get_permalink( pno_get_password_recovery_page_id() );
			}
			break;
		case 'registration':
			if ( is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				$menu_item->url = get_permalink( pno_get_registration_page_id() );
			}
			break;
		case 'logout':
			if ( ! is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				$menu_item->url = wp_logout_url();
			}
			break;
		case 'dashboard':
			if ( ! is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				$menu_item->url = get_permalink( pno_get_dashboard_page_id() );
			}
			break;
		case 'edit-account':
		case 'password':
		case 'privacy':
			if ( ! is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				$menu_item->url = pno_get_dashboard_navigation_item_url( $matches[1] );
			}
			break;
	}

	$menu_item->pno_identifier = $matches[1];

	return $menu_item;

}
add_filter( 'wp_setup_nav_menu_item', 'pno_setup_nav_menu_item', 10, 1 );

/**
 * When deleting a user, delete all listings assigned to him.
 *
 * @param array $types list of post types.
 * @return array
 */
function pno_delete_listings_on_user_delete( $types ) {
	$types[] = 'listings';
	return $types;
}
add_filter( 'post_types_to_delete_with_user', 'pno_delete_listings_on_user_delete', 10 );
