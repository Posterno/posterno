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
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_website( $email ) {
	return home_url();
}

/**
 * Parse the {sitename} tag into the email to display the site name.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_sitename( $email ) {
	return esc_html( get_bloginfo( 'name' ) );
}

/**
 * Parse the {username} tag into the email to display the user's username.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_username( $email ) {
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
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_email( $email ) {
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
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_password( $email ) {
	return sanitize_text_field( $plain_text_password );
}

/**
 * Parse the {login_page_url} tag into the email to display the site login page url.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_login_page_url( $email ) {
	$login_page_url = pno_get_login_page_id();
	$login_page_url = get_permalink( $login_page_url );
	return $login_page_url;
}

/**
 * Parse the {recovery_url} tag into the email to display personalized password recovery url.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_password_recovery_url( $email ) {
	$reset_page = pno_get_password_recovery_page_id();
	$reset_page = get_permalink( $reset_page );
	$reset_page = add_query_arg(
		[
			'user_id' => $user_id,
			'key'     => $password_reset_key,
			'step'    => 'reset',
		],
		$reset_page
	);
	$output     = $reset_page;
	return $output;
}

/**
 * Display the ID number of a listing within emails.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_listing_id_number( $email ) {
	return $listing_id ? absint( $listing_id ) : '-';
}

/**
 * Display title of the listing within emails.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_listing_title( $email ) {
	return get_the_title( $listing_id );
}

/**
 * Display the publish date of the listing within emails.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_listing_submission_date( $email ) {
	return get_the_date( get_option( 'date_format' ), $listing_id );
}

/**
 * Display the url of the listing within emails.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_listing_url( $email ) {
	return get_permalink( $listing_id );
}

/**
 * Display the listing expiration date within emails.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_listing_expiry_date( $email ) {
	return pno_get_the_listing_expire_date( $listing_id );
}

/**
 * Displays the name of the sender of the message. This is usually used within contact forms.
 * Example: the listing's contact form widget.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_sender_name( $email ) {
	return isset( $email->sender_name ) ? esc_html( $email->sender_name ) : '';
}

/**
 * Displays the email of the sender of the message. This is usually used within contact forms.
 * Example: the listing's contact form widget.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_sender_email( $email ) {
	return isset( $email->sender_email ) ? sanitize_email( $email->sender_email ) : '';
}

/**
 * Displays the message of the sender. This is usually used within contact forms.
 * Example: the listing's contact form widget.
 *
 * @param PNO_Emails $email the email's object.
 * @return string
 */
function pno_email_tag_sender_message( $email ) {
	return isset( $email->sender_message ) ? wp_strip_all_tags( stripslashes( $email->sender_message ) ) : '';
}

/**
 * Disable the email notification sent to the admin when a user changes the password.
 */
if ( pno_get_option( 'disable_admin_password_recovery_email' ) && ! function_exists( 'wp_password_change_notification' ) ) {
	function wp_password_change_notification( $user ) {
		return false;
	}
}

/**
 * Get a list of emails for populating email type taxonomy terms.
 *
 * @return array {
 *     The array of email types and their schema.
 *
 *     @type string $description The description of the action which causes this to trigger.
 *     @type array  $unsubscribe {
 *         Replacing this with false indicates that a user cannot unsubscribe from this type.
 *
 *         @type string $meta_key The meta_key used to toggle the email setting for this notification.
 *         @type string $message  The message shown when the user has successfully unsubscribed.
 *     }
 */
function pno_email_get_type_schema() {

	$types = array(
		'core_user_registration'      => [
			'description' => esc_html__( 'Recipient has registered for an account.' ),
			'unsubscribe' => false,
		],
		'core_user_password_recovery' => [
			'description' => esc_html__( 'Recipient has forgotten his password.' ),
			'unsubscribe' => false,
		],
		'core_user_listing_submitted' => [
			'description' => esc_html__( 'Recipient has successfully submitted a listing.' ),
		],
		'core_user_listing_updated'   => [
			'description' => esc_html__( 'Recipient has successfully updated a listing.' ),
		],
		'core_user_listing_approved'  => [
			'description' => esc_html__( 'Administrator has approved a listing for the recipient.' ),
		],
		'core_listing_expiring'       => [
			'description' => __( 'Recipient\'s listing is about to expire.' ),
		],
		'core_listing_author_email'   => [
			'description' => __( 'Listing\'s author received an email.' ),
		],
	);

	return $types;
}

/**
 * Retrieve emails by email type from the database.
 *
 * @param string $email_type the type of situation to retrieve from the database.
 * @return mixed
 */
function pno_get_emails( $email_type ) {

	$args = array(
		'no_found_rows'    => true,
		'numberposts'      => 3,
		'post_status'      => 'publish',
		'post_type'        => 'pno_emails',
		'suppress_filters' => false,
		'tax_query'        => array(
			array(
				'field'    => 'slug',
				'taxonomy' => 'pno-email-type',
				'terms'    => $email_type,
			),
		),
	);

	/**
	 * Filters arguments used to find an email post type object.
	 *
	 * @param array  $args       Arguments for get_posts() used to fetch a post object.
	 * @param string $email_type Unique identifier for a particular type of email.
	 */
	$args = apply_filters( 'pno_send_email_args', $args, $email_type );

	$emails = get_posts( $args );

	if ( ! $emails ) {
		return new WP_Error( 'missing_email', __FUNCTION__, array( $email_type, $args ) );
	}

	return $emails;

}

/**
 * Send emails.
 *
 * @param string $email_type the type of the email to send.
 * @param string $to the email address to which we're going to send emails.
 * @param array  $args additional settings to pass to the PNO_Emails class.
 * @return mixed
 */
function pno_send_email( $email_type, $to, $args = [] ) {

	if ( ! $to ) {
		return;
	}

	$emails = pno_get_emails( $email_type );

	if ( is_wp_error( $emails ) ) {
		return $emails;
	}

	if ( ! empty( $args ) ) {
		foreach ( $args as $key => $value ) {
			if ( $value ) {
				posterno()->emails->__set( $key, $value );
			}
		}
	}

	foreach ( $emails as $email_to_send ) {

		$email_settings         = get_post_meta( $email_to_send->ID, 'email_settings', true );
		$has_admin_notification = isset( $email_settings['_has_admin_notification'] ) ? true : false;

		$subject = $email_to_send->post_title;
		$message = $email_to_send->post_content;
		$heading = isset( $email_settings['_heading'] ) ? $email_settings['_heading'] : false;

		if ( $heading ) {
			posterno()->emails->__set( 'heading', $heading );
		}

		// Send the email to the end user only if a subject and content is specified.
		if ( ! $subject || empty( $subject ) || ! $message || empty( $message ) ) {
			return;
		}

		posterno()->emails->send( $to, $subject, $message );

		if ( $has_admin_notification ) {

			$admin_to           = get_option( 'admin_email' );
			$admin_notification = isset( $email_settings['_administrator_notification'] ) ? wpautop( $email_settings['_administrator_notification'] ) : false;
			$admin_subject      = isset( $email_settings['_administrator_notification_subject'] ) ? $email_settings['_administrator_notification_subject'] : false;

			posterno()->emails->send( $admin_to, $admin_subject, $admin_notification );

		}
	}

}
