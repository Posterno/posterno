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

		}

		return ob_get_clean();

	}

}

( new LoginForm() )->hook();
