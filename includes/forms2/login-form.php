<?php
/**
 * Handle the login form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Forms;

use PNO\Form;
use PNO\Forms;
use PNO\Form\Field\TextField;
use PNO\Form\Field\PasswordField;
use PNO\Form\Field\CheckboxField;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle the Posterno's login form.
 */
class LoginForm extends Forms {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form_name    = 'login_form';
		$this->submit_label = esc_html__( 'Login' );
		parent::__construct();
	}

	/**
	 * Define the list of fields for the form.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = array(
			new TextField(
				'username',
				[
					'label' => \pno_get_login_label(),
				]
			),
			new PasswordField(
				'password',
				array(
					'label' => esc_html__( 'Password' ),
				)
			),
			new CheckboxField(
				'remember_me',
				array(
					'label' => esc_html__( 'Remember me' ),
				)
			),
		);

		/**
		 * Allows developers to register or deregister fields for the login form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the login form.
		 * @param Form  $form the form object.
		 */
		return apply_filters( 'pno_login_form_fields', $fields, $this->form );

	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
		add_shortcode( 'pno_login_form', [ $this, 'shortcode' ] );
	}

	/**
	 * Login form shortcode.
	 *
	 * @return string
	 */
	public function shortcode() {

		ob_start();

		if ( is_user_logged_in() ) {

			$data = [
				'user' => wp_get_current_user(),
			];

			posterno()->templates
				->set_template_data( $data )
				->get_template_part( 'logged-user' );

		} else {

			posterno()->templates
				->set_template_data(
					[
						'form'         => $this->form,
						'submit_label' => $this->submit_label,
					]
				)
				->get_template_part( 'form' );

			$action_links = [
				'register_link' => pno_get_option( 'login_show_registration_link' ),
				'psw_link'      => pno_get_option( 'login_show_password_link' ),
			];

			posterno()->templates
				->set_template_data( $action_links )
				->get_template_part( 'forms/action-links' );

		}

		return ob_get_clean();

	}

	/**
	 * Process the form.
	 *
	 * @throws \Exception When authentication process fails.
	 * @return void
	 */
	public function process() {
		try {
			//phpcs:ignore
			if ( empty( $_POST[ 'submit_' . $this->form->get_name() ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ "{$this->form->get_name()}_nonce" ], "verify_{$this->form->get_name()}_form" ) ) {
				return;
			}

			if ( ! isset( $_POST[ $this->form->get_name() ] ) ) {
				return;
			}

			$this->form->bind( $_POST[ $this->form->get_name() ] );

			if ( $this->form->is_valid() ) {

				$values = $this->form->get_data();

				$username = $values['username'];
				$password = $values['password'];

				$authenticate = wp_authenticate( $username, $password );

				if ( is_wp_error( $authenticate ) ) {
					throw new \Exception( $authenticate->get_error_message() );
				} else {

					$creds = [
						'user_login'    => $username,
						'user_password' => $password,
						'remember'      => $values['remember_me'] ? true : false,
					];

					$user = wp_signon( $creds );

					if ( is_wp_error( $user ) ) {
						throw new Exception( $user->get_error_message() );
					} else {
						wp_safe_redirect( pno_get_login_redirect() );
						exit;
					}

				}

			}
		} catch ( \Exception $e ) {
			$this->form->set_processing_error( $e->getMessage() );
			return;
		}

	}

}

( new LoginForm() )->hook();
