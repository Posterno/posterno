<?php
/**
 * Shortcodes definition
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use PNO\Form;
use PNO\Form\Field\TextField;
use PNO\Form\Field\TextAreaField;
use PNO\Form\Field\CheckboxField;
use PNO\Form\Field\DropdownField;
use PNO\Form\Field\EditorField;
use PNO\Form\Field\EmailField;
use PNO\Form\Field\MultiCheckboxField;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function testshortcode() {
	$form = new Form(
		'Contact_Form', array(
			new TextField(
				'first_name',
				array(
					'label' => 'First Name:',
				)
			),
			new CheckboxField(
				'first_name_n',
				array(
					'label' => 'First Name:',
				)
			),
			new MultiCheckboxField(
				'last_name',
				array(
					'label' => 'Last Name:',
					'value' => [
						'val2'
					],
					'choices' => [
						'val' => 'val 1',
						'val2' => 'val 2'
					]
				)
			),
		)
	);

	ob_start();

	posterno()->templates
		->set_template_data(
			[
				'form'         => $form,
				'submit_label' => 'submit',
			]
		)
		->get_template_part( 'form' );

	return ob_get_clean();

}
add_shortcode( 'testshortcode', 'testshortcode' );

/**
 * Displays the login form to visitors and display a notice to logged in users.
 *
 * @return string
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
 * @return string
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
 * @return string
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
 * @param array  $atts attributes list of the shortcode.
 * @param string $content content added within the shortcode.
 * @return string
 */
function pno_login_link( $atts, $content = null ) {
	// phpcs:ignore
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

/**
 * Display a logout link.
 *
 * @param array  $atts attributes list of the shortcode.
 * @param string $content content added within the shortcode.
 * @return string
 */
function pno_logout_link( $atts, $content = null ) {
	// phpcs:ignore
	extract(
		shortcode_atts(
			array(
				'redirect' => '',
				'label'    => esc_html__( 'Logout' ),
			), $atts
		)
	);
	$output = '';
	if ( is_user_logged_in() ) {
		$output = '<a href="' . esc_url( wp_logout_url( $redirect ) ) . '">' . esc_html( $label ) . '</a>';
	}
	return $output;
}
add_shortcode( 'pno_logout_link', 'pno_logout_link' );

/**
 * Displays the dashboard for the listings.
 *
 * @return string
 */
function pno_dashboard() {

	ob_start();

	posterno()->templates->get_template_part( 'dashboard' );

	return ob_get_clean();

}
add_shortcode( 'pno_dashboard', 'pno_dashboard' );

/**
 * Display the listings submission form.
 *
 * @return string
 */
function pno_submit_listing_form() {

	ob_start();

	$account_required = pno_get_option( 'submission_requires_account' );
	$roles_required   = pno_get_option( 'submission_requires_roles' );

	/**
	 * Allow developers to add custom access restrictions to the submission form.
	 *
	 * @param bool $restricted true or false.
	 * @return bool|string
	 */
	$restricted = apply_filters( 'pno_submission_form_is_restricted', false );

	// Display error message if specific roles are required to access the page.
	if ( is_user_logged_in() && $account_required && $roles_required && is_array( $roles_required ) && ! empty( $roles_required ) ) {

		$user           = wp_get_current_user();
		$role           = (array) $user->roles;
		$roles_selected = [ 'administrator' ];

		foreach ( $roles_required as $single_role ) {
			$roles_selected[] = $single_role['value'];
		}

		if ( ! array_intersect( (array) $user->roles, $roles_selected ) ) {
			$restricted = 'role';
		}
	}

	if ( $restricted ) {

		/**
		 * Allow developers to customize the restriction message for the submission form.
		 *
		 * @param string $message the restriction message.
		 * @param bool|string $restricted wether it's restricted or not and what type of restriction.
		 */
		$message = apply_filters( 'pno_submission_restriction_message', esc_html__( 'Access to this page is restricted.' ), $restricted );

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'warning',
					'message' => $message,
				]
			)
			->get_template_part( 'message' );

	} else {

		echo posterno()->forms->get_form( 'listing-submit' ); //phpcs:ignore

	}

	return ob_get_clean();

}
add_shortcode( 'pno_listing_submission_form', 'pno_submit_listing_form' );

