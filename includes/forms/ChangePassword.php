<?php
/**
 * Handles display and processing of the change password form.
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
class ChangePassword {

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
	public $form_name = 'change-password';

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form = Form::createFromConfig( $this->getFields() );
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
		add_shortcode( 'pno_change_password_form', [ $this, 'render' ] );
		add_action( 'wp_loaded', [ $this, 'process' ] );
	}

	/**
	 * Get fields for the form.
	 *
	 * @return array
	 */
	protected function getFields() {

		$fields = [
			'password_current' => [
				'type'       => 'password',
				'label'      => esc_html__( 'Current password', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 1,
			],
			'password'         => [
				'type'       => 'password',
				'label'      => esc_html__( 'New password', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 2,
			],
			'password_confirm' => [
				'type'       => 'password',
				'label'      => esc_html__( 'Repeat new password', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 3,
			],
			/**
			 * Honeypot field.
			 */
			'hp-comments'      => [
				'type'       => 'text',
				'label'      => esc_html__( 'If you\'re human leave this blank:', 'posterno' ),
				'validators' => new Validator\BeEmpty(),
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 4,
			],
			'submit'           => [
				'type'       => 'button',
				'value'      => esc_html__( 'Change password', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 100,
			],
		];

		// Verify strong passwords if enabled.
		if ( pno_get_option( 'strong_passwords' ) ) {
			$error_message    = esc_html__( 'Password must be at least 8 characters long and contain at least 1 number, 1 uppercase letter and 1 special character.', 'posterno' );
			$contains_letter  = new Validator\RegEx( '/[A-Z]/', $error_message );
			$contains_digit   = new Validator\RegEx( '/\d/', $error_message );
			$contains_special = new Validator\RegEx( '/[^a-zA-Z\d]/', $error_message );
			$lenght           = new Validator\LengthGreaterThanEqual( 8, $error_message );

			$fields['password']['validators']         = [
				$contains_letter,
				$contains_digit,
				$contains_special,
				$lenght,
			];
			$fields['password_confirm']['validators'] = [
				$contains_letter,
				$contains_digit,
				$contains_special,
				$lenght,
			];
		}

		/**
		 * Filter: allows customization of the fields for the change password form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_change_password_form_fields', $fields );

		uasort( $fields, 'pno_sort_array_by_priority' );

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

			$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
						'title'     => esc_html__( 'Change password', 'posterno' ),
					]
				)
				->get_template_part( 'new-form' );

		}

		return ob_get_clean();

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

				$current_password     = $this->form->getFieldValue( 'password_current' );
				$new_password         = $this->form->getFieldValue( 'password' );
				$new_password_confirm = $this->form->getFieldValue( 'password_confirm' );

				if ( $new_password !== $new_password_confirm ) {
					throw new Exception( esc_html__( 'Passwords do not match.', 'posterno' ) );
				}

				$user = wp_get_current_user();

				if ( $user instanceof \WP_User && wp_check_password( $current_password, $user->data->user_pass, $user->ID ) && is_user_logged_in() ) {
					$updated_user_id = wp_update_user(
						[
							'ID'        => $user->ID,
							'user_pass' => $new_password,
						]
					);

					if ( is_wp_error( $updated_user_id ) ) {
						throw new Exception( $updated_user_id->get_error_message(), $updated_user_id->get_error_code() );
					} else {

						/**
						 * Allow developers to customize the confirmation message when a user changes his password.
						 *
						 * @param string $message the message.
						 * @return string
						 */
						$message = apply_filters( 'pno_password_change_confirmation_message', esc_html__( 'Password successfully updated.', 'posterno' ) );

						$this->form->setSuccessMessage( $message );
						$this->form->reset();
						return;
					}
				} else {
					throw new Exception( __( 'The password you entered is incorrect.', 'posterno' ) );
				}
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}

( new ChangePassword() )->init();
