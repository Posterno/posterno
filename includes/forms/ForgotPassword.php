<?php
/**
 * Handles display and processing of the forgot password recovery form.
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
use PNO\Form\DefaultSanitizer;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class handling the password recovery form.
 */
class ForgotPassword {

	use DefaultSanitizer;

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
	public $form_name = 'forgotPassword';

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
		$this->addSanitizer( $this->form );
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

		$fields = [];

		if ( $this->isRecoveryMode() ) {
			$fields = $this->getUpdateFields();
		} else {
			$fields = $this->getRequestFields();
		}

		$necessaryFields = [
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
			'submit-form' => [
				'type'       => 'button',
				'value'      => esc_html__( 'Reset password', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 100,
			],
		];

		$fields = array_merge( $fields, $necessaryFields );

		/**
		 * Filter: allows developers to register or deregister fields for the password recovery form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the password recovery form.
		 * @param boolean $recoveryMode determine if recovery mode has been triggered or not.
		 */
		$fields = apply_filters( 'pno_forgot_password_form_fields', $fields, $this->isRecoveryMode() );

		uasort( $fields, 'pno_sort_array_by_priority' );

		return $fields;

	}

	/**
	 * Get fields belonging to the first step of the recovery process.
	 *
	 * @return array
	 */
	private function getRequestFields() {

		$fields = [
			'username_email' => [
				'type'       => 'text',
				'label'      => esc_html__( 'Username or email', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 1,
			],
		];

		return $fields;

	}

	/**
	 * Get fields belonging to the update process of the recovery process.
	 *
	 * @return array
	 */
	private function getUpdateFields() {

		$fields = [
			'password'         => [
				'type'       => 'password',
				'label'      => esc_html__( 'New password', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 1,
			],
			'password_confirm' => [
				'type'       => 'password',
				'label'      => esc_html__( 'Re-enter new password', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 2,
			],
		];

		return $fields;

	}

	/**
	 * Determine if we've got a key and user id to display the password fields and update the password.
	 *
	 * @return boolean
	 */
	private function isRecoveryMode() {
		return isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) && isset( $_GET['key'] ) && ! empty( $_GET['key'] ) ? true : false;
	}

	/**
	 * Render the form.
	 *
	 * @return string
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

			$this->form->filterValues();
			$this->form->prepareForView();

			/**
			 * Filter: allow developers to customize the lost password form message.
			 *
			 * @param string $message the message to display.
			 */
			$message = apply_filters( 'pno_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'posterno' ) );

			if ( $this->isRecoveryMode() ) {

				/**
				 * Filter: allow developers to customize the message that appears when entering a new password
				 * during the password recovery form.
				 *
				 * @param string $message
				 */
				$message = apply_filters( 'pno_change_password_message', esc_html__( 'Create a new password for the account.', 'posterno' ) );
			}

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
						'message'   => $message,
					]
				)
				->get_template_part( 'form' );

			$action_links = [
				'login_link'    => pno_get_option( 'recovery_show_login_link' ),
				'register_link' => pno_get_option( 'recovery_show_registration_link' ),
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

				/**
				 * Hook: allow developers to hook into the password recovery process.
				 *
				 * @param Form $form
				 */
				do_action( 'pno_before_password_recovery', $this->form );

				$username = $this->form->getFieldValue( 'username_email' );
				$user     = false;

				// Update the password step.
				if ( $this->isRecoveryMode() ) {

					$user_id          = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;
					$get_user         = get_user_by( 'id', $user_id );
					$verification_key = isset( $_GET['key'] ) && ! empty( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : false;

					if ( $user_id && $get_user instanceof \WP_User && $verification_key ) {

						/**
						 * Filters whether to allow a password to be reset.
						 *
						 * @param bool $allow   Whether to allow the password to be reset. Default true.
						 * @param int  $user_id The ID of the user attempting to reset a password.
						 */
						$allow = apply_filters( 'allow_password_reset', true, $user_id );

						if ( ! $allow ) {
							throw new Exception( esc_html__( 'Password reset is not allowed for this user.', 'posterno' ) );
						}

						$verify_key = check_password_reset_key( $verification_key, $get_user->data->user_login );

						if ( is_wp_error( $verify_key ) ) {

							/**
							 * Allow developers to customize the error message that appears
							 * when the password reset key is invalid.
							 *
							 * @param string $message the error message.
							 * @return string the new message.
							 */
							$error_message = apply_filters( 'pno_password_recovery_key_invalid_message', esc_html__( 'The reset key is wrong or expired. Please check that you used the right reset link or request a new one.', 'posterno' ) );

							throw new Exception( $error_message );

						} else {

							$password_1 = $this->form->getFieldValue( 'password' );
							$password_2 = $this->form->getFieldValue( 'password_confirm' );

							if ( $password_1 !== $password_2 ) {

								$error_not_matching = esc_html__( 'Passwords do not match.', 'posterno' );

								/**
								 * Allow developers to customize the password recovery psw not matching message.
								 *
								 * @param string $message the error message.
								 * @return string the new message.
								 */
								$error_not_matching = apply_filters( 'pno_password_recovery_not_matching_message', $error_not_matching );

								throw new Exception( $error_not_matching );
							}

							$user_id = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;

							wp_set_password( $password_1, $user_id );

							// Clear all user sessions.
							$sessions = \WP_Session_Tokens::get_instance( $user_id );
							$sessions->destroy_all();

							$success_message = esc_html__( 'Password successfully reset.', 'posterno' ) . ' ' . '<a href="' . esc_url( get_permalink( pno_get_login_page_id() ) ) . '">' . esc_html__( 'Login now &raquo;', 'posterno' ) . '</a>';

							/**
							 * Allow developers to customize the password recovery success message.
							 *
							 * @param string $message the success message.
							 * @return string the new message.
							 */
							$success_message = apply_filters( 'pno_password_recovery_success_message', $success_message );

							$this->form->setSuccessMessage( $success_message );
							$this->form->reset();
							return;
						}
					} else {

						/**
						 * Filter: allow developers to customize the password recovery invalid link message.
						 *
						 * @param string $message the error message.
						 * @return string the new message.
						 */
						$invalid_link = apply_filters( 'pno_password_recovery_invalid_link_message', esc_html__( 'The link you followed may be broken. Please check that you used the right reset link or request a new one.', 'posterno' ) );

						throw new Exception( $invalid_link );
					}
				} else {

					if ( is_email( $username ) && ! email_exists( $username ) || ! is_email( $username ) && ! username_exists( $username ) ) {
						/**
						 * Filter: allow developers to customize the password recovery user not found message.
						 *
						 * @param string $message the error message.
						 * @return string the new message.
						 */
						$user_not_found_message = apply_filters( 'pno_password_recovery_invalid_user', esc_html__( 'A user with this username or email does not exist. Please check your entry and try again.', 'posterno' ) );

						throw new Exception( $user_not_found_message );
					}

					if ( is_email( $username ) ) {
						$user = get_user_by( 'email', $username );
					} else {
						$user = get_user_by( 'login', $username );
					}

					if ( $user instanceof \WP_User ) {

						/**
						 * Filters whether to allow a password to be reset.
						 *
						 * @param bool $allow   Whether to allow the password to be reset. Default true.
						 * @param int  $user_id The ID of the user attempting to reset a password.
						 */
						$allow = apply_filters( 'allow_password_reset', true, $user->data->ID );

						if ( ! $allow ) {
							throw new Exception( esc_html__( 'Password reset is not allowed for this user.', 'posterno' ) );
						}

						// Generate a new password reset key for the selected user.
						$password_reset_key = get_password_reset_key( $user );

						// Now send an email to the user.
						if ( $password_reset_key ) {

							// Send password recovery email.
							pno_send_email(
								'core_user_password_recovery',
								$user->data->user_email,
								[
									'user_id'            => $user->data->ID,
									'password_reset_key' => $password_reset_key,
								]
							);

							$masked_email    = pno_mask_email_address( $user->data->user_email );
							$success_message = sprintf( esc_html__( 'We\'ve sent an email to %s with password reset instructions.', 'posterno' ), '<strong>' . $masked_email . '</strong>' );

							/**
							 * Filter: allow developers to customize the success message for the password
							 * recovery form when the recovery email has been sent.
							 *
							 * @param string $success_message the message.
							 * @return string the new message.
							 */
							$success_message = apply_filters( 'pno_password_recovery_success_mail_sent', $success_message );

							$this->form->setSuccessMessage( $success_message );
							$this->form->reset();
							return;
						}
					} else {
						throw new Exception( esc_html__( 'Something went wrong.', 'posterno' ) );
					}
				}
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
