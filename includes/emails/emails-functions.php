<?php
/**
 * Collection of functions directly related to the emails.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the list of available email templates.
 *
 * @return array
 */
function pno_get_email_templates() {
	return posterno()->emails->get_templates();
}

/**
 * Retrieve a formatted list of all registered email tags.
 *
 * @return string
 */
function pno_get_emails_tags_list() {
	$list       = '';
	$email_tags = posterno()->emails->get_tags();
	if ( count( $email_tags ) > 0 ) {
		foreach ( $email_tags as $email_tag ) {
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br />';
		}
	}
	return $list;
}

/**
 * Parse the {website} tag into the email to display the site url.
 *
 * @param string $user_id the id of the user being processed.
 * @return string
 */
function pno_email_tag_website( $user_id ) {
	return home_url();
}

/**
 * Parse the {sitename} tag into the email to display the site name.
 *
 * @param string $user_id the id of the user being processed.
 * @return string
 */
function pno_email_tag_sitename( $user_id ) {
	return esc_html( get_bloginfo( 'name' ) );
}

/**
 * Parse the {username} tag into the email to display the user's username.
 *
 * @param string $user_id the id of the user being processed.
 * @return string
 */
function pno_email_tag_username( $user_id ) {
	$user     = get_user_by( 'id', $user_id );
	$username = '';
	if ( $user instanceof WP_User ) {
		$username = $user->data->user_login;
	}
	return $username;
}

/**
 * Parse the {email} tag into the email to display the user's email.
 *
 * @param string $user_id the id of the user being processed.
 * @return string
 */
function pno_email_tag_email( $user_id ) {
	$user  = get_user_by( 'id', $user_id );
	$email = '';
	if ( $user instanceof WP_User ) {
		$email = $user->data->user_email;
	}
	return $email;
}

/**
 * Parse the password tag within the emails.
 *
 * @param string  $user_id the id of the user being processed.
 * @param boolean $password_reset_key the password reset key.
 * @param string  $plain_text_password plain text password.
 * @return string
 */
function pno_email_tag_password( $user_id = false, $password_reset_key = false, $plain_text_password ) {
	return sanitize_text_field( $plain_text_password );
}

/**
 * Parse the {login_page_url} tag into the email to display the site login page url.
 *
 * @param string $user_id the id of the user being processed.
 * @return string
 */
function pno_email_tag_login_page_url( $user_id = false ) {
	$login_page_url = pno_get_login_page_id();
	$login_page_url = get_permalink( $login_page_url );
	return $login_page_url;
}

/**
 * Parse the {recovery_url} tag into the email to display personalized password recovery url.
 *
 * @param string  $user_id the id of the user being processed.
 * @param boolean $password_reset_key the password reset key.
 * @return string
 */
function pno_email_tag_password_recovery_url( $user_id, $password_reset_key ) {
	$reset_page = pno_get_password_recovery_page_id();
	$reset_page = get_permalink( $reset_page );
	$reset_page = add_query_arg(
		[
			'user_id' => $user_id,
			'key'     => $password_reset_key,
			'step'    => 'reset',
		], $reset_page
	);
	$output     = $reset_page;
	return $output;
}

/**
 * Disable the email notification sent to the admin when a user changes the password.
 */
if ( pno_get_option( 'disable_admin_password_recovery_email' ) && ! function_exists( 'wp_password_change_notification' ) ) {
	function wp_password_change_notification( $user ) {
		return false;
	}
}
