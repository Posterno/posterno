<?php
/**
 * Collection of functions directly related to the emails.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
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
 * @param string $user_id
 * @return void
 */
function pno_email_tag_website( $user_id ) {
	return home_url();
}

/**
 * Parse the {sitename} tag into the email to display the site name.
 *
 * @param string $user_id
 * @return void
 */
function pno_email_tag_sitename( $user_id ) {
	return esc_html( get_bloginfo( 'name' ) );
}

/**
 * Parse the {username} tag into the email to display the user's username.
 *
 * @param string $user_id
 * @return void
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
 * @param string $user_id
 * @return void
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
 * @param boolean $user_id
 * @param boolean $password_reset_key
 * @param string $plain_text_password
 * @return void
 */
function pno_email_tag_password( $user_id = false, $password_reset_key = false, $plain_text_password ) {
	return sanitize_text_field( $plain_text_password );
}
