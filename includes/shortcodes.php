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

/**
 * Displays the password recovery form to visitors and a notice to logged in users.
 *
 * @return void
 */
function pno_password_recovery_form() {

	ob_start();

	if ( is_user_logged_in() ) {

		$data = [
			'user' => wp_get_current_user(),
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'logged-user' );

	} else {
		echo posterno()->forms->get_form( 'password-recovery', [] );
	}

	return ob_get_clean();

}
add_shortcode( 'pno_password_recovery_form', 'pno_password_recovery_form' );

/**
 * Display a login link.
 *
 * @param array $atts
 * @param string $content
 * @return void
 */
function pno_login_link( $atts, $content = null ) {
	extract(
		shortcode_atts(
			array(
				'redirect' => '',
				'label'    => esc_html__( 'Login' ),
			), $atts
		)
	);
	if ( is_user_logged_in() ) {
		$output = '';
	} else {
		$url    = wp_login_url( $redirect );
		$output = '<a href="' . esc_url( $url ) . '" class="pno-login-link">' . esc_html( $label ) . '</a>';
	}
	return $output;
}
add_shortcode( 'pno_login_link', 'pno_login_link' );
