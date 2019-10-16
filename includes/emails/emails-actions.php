<?php
/**
 * Responsible of sending emails at the right time.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Send the registration confirmation email.
 *
 * @param string $new_user_id the user id.
 * @param object $form the form.
 * @param string $email_address the address.
 * @param string $password password.
 * @return void
 */
function pno_send_registration_confirmation_email( $new_user_id, $form, $email_address, $password ) {

	pno_send_email(
		'core_user_registration',
		$email_address,
		[
			'user_id'             => $new_user_id,
			'plain_text_password' => $password,
		]
	);

}
add_action( 'pno_do_registration_email', 'pno_send_registration_confirmation_email', 10, 4 );
