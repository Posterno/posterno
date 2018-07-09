<?php
/**
 * Shortcodes definition
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Displays the login form to visitors and display a notice to logged in users.
 *
 * @return void
 */
function pno_login_form() {

	ob_start();

	if ( is_user_logged_in() ) {

		$data = [
			'user' => wp_get_current_user(),
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'logged-user' );

	} else {
		echo posterno()->forms->get_form( 'login', [] );
	}

	return ob_get_clean();

}
add_shortcode( 'pno_login_form', 'pno_login_form' );

/**
 * Displays the registration form to visitors and displays a notice to logged in users.
 *
 * @return void
 */
function pno_registration_form() {

	ob_start();

	if ( is_user_logged_in() ) {

		$data = [
			'user' => wp_get_current_user(),
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'logged-user' );

	} else {
		echo posterno()->forms->get_form( 'registration', [] );
	}

	return ob_get_clean();

}
add_shortcode( 'pno_registration_form', 'pno_registration_form' );
