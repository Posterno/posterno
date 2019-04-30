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
use PNO\Exception;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class of the login form.
 */
class Login {

	/**
	 * The form object containing all the details about the form.
	 *
	 * @var Form
	 */
	public $form;

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'login';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Login The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Returns static instance of class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form = Form::createFromConfig( $this->getFields() );
		$this->init();
	}

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
		add_action( 'wp_loaded', [ $this, 'process' ] );
	}

	/**
	 * Get fields for the form.
	 *
	 * @return array
	 */
	protected function getFields() {

		$fields = [
			'username'    => [
				'type'       => 'text',
				'label'      => pno_get_login_label(),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 1,
			],
			'password'    => [
				'type'       => 'password',
				'label'      => esc_html__( 'Password', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 2,
			],
			'remember'    => array(
				'type'       => 'checkbox',
				'value'      => 1,
				'label'      => esc_html__( 'Remember me', 'posterno' ),
				'required'   => false,
				'attributes' => [
					'class' => 'custom-control-input',
				],
				'priority'   => 3,
			),
			/**
			 * Honeypot field.
			 */
			'hp-comments' => [
				'type'       => 'text',
				'label'      => esc_html__( 'If you\'re human leave this blank:', 'posterno' ),
				'validators' => new Validator\BeEmpty(),
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 4,
			],
			'submit'      => [
				'type'       => 'button',
				'value'      => esc_html__( 'Login', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 100,
			],
		];

		/**
		 * Filter: allows customization of the fields for the login form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_login_form_fields', $fields );

		uasort( $fields, 'pno_sort_array_by_priority' );

		return $fields;

	}

	/**
	 * Render the form.
	 *
	 * @return void
	 */
	public function render() {

		if ( is_user_logged_in() ) {

			$data = [
				'user' => wp_get_current_user(),
			];

			posterno()->templates
				->set_template_data( $data )
				->get_template_part( 'logged-user' );

		} else {

			$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
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

	}

	/**
	 * Process the form.
	 *
	 * @throws Exception When there's an error during credentials process.
	 * @return void
	 */
	public function process() {
		try {

			//phpcs:ignore
			if ( ! isset( $_POST[ 'pno_form' ] ) || isset( $_POST['pno_form'] ) && $_POST['pno_form'] !== $this->form_name ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ "{$this->form_name}_nonce" ], "verify_{$this->form_name}_form" ) ) {
				return;
			}

			$this->form->setFieldValues( $_POST );

			if ( $this->form->isValid() ) {

				$username    = $this->form->getFieldValue( 'username' );
				$password    = $this->form->getFieldValue( 'password' );
				$remember_me = $this->form->getFieldValue( 'remember' );

				$creds = [
					'user_login'    => $username,
					'user_password' => $password,
					'remember'      => $remember_me,
				];

				$user = wp_signon( $creds );

				if ( is_wp_error( $user ) ) {
					throw new Exception( $user->get_error_message(), $user->get_error_code() );
				} else {
					wp_safe_redirect( pno_get_login_redirect() );
					exit;
				}
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
