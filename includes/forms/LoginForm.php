<?php
/**
 * Handles display and processing of the login form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018 - 2019, Sematico, LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Forms;

use PNO\Form\Form;
use PNO\Validator;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class LoginForm {

	/**
	 * Get things started.
	 *
	 * @return void
	 */
	public function init() {
		$this->hook();
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_shortcode( 'pno_login_form', [ $this, 'render' ] );
	}

	/**
	 * Get fields for the form.
	 *
	 * @return array
	 */
	protected function getFields() {

		$fields = [
			'username' => [
				'type'       => 'text',
				'label'      => pno_get_login_label(),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
			],
			'password' => [
				'type'       => 'password',
				'label'      => esc_html__( 'Password', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
			],
			'remember' => array(
				'type'     => 'check',
				'label'    => esc_html__( 'Remember me', 'posterno' ),
				'required' => false,
				'attributes' => [
					'class' => 'form-check-input',
				],
			),
			'submit'   => [
				'type'       => 'button',
				'value'      => esc_html__( 'Login', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
			],
		];

		return $fields;

	}

	/**
	 * Render the form.
	 *
	 * @return string
	 */
	public function render() {

		ob_start();

		if ( is_user_logged_in() ) {

			$data = [
				'user' => wp_get_current_user(),
			];

			posterno()->templates
				->set_template_data( $data )
				->get_template_part( 'logged-user' );

		} else {

			$form = Form::createFromConfig( $this->getFields() );

			posterno()->templates
				->set_template_data(
					[
						'form' => $form,
					]
				)
				->get_template_part( 'new-form' );

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

}

( new LoginForm() )->init();
