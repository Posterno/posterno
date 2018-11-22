<?php
/**
 * Handles display and processing of the password recovery form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles the login form.
 */
class PNO_Form_Password_Recovery extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'password-recovery';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Password_Recovery The single instance of the class
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
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'process' ) );

		$steps = array(
			'submit' => array(
				'name'     => esc_html__( 'Password recovery details request' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
		);

		/**
		 * List of steps defined for the password recovery form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the password recovery form.
		 */
		$this->steps = (array) apply_filters( 'pno_password_recovery_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( intval( $_POST['step'] ), array_keys( $this->steps ), true );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( intval( $_GET['step'] ), array_keys( $this->steps ), true );
		}

	}

	/**
	 * Initializes the fields used in the form.
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$fields = array(
			'user'     => array(
				'username_email' => array(
					'label'       => __( 'Username or email' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1,
				),
			),
			'password' => array(
				'password'   => array(
					'label'       => __( 'New password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1,
				),
				'password_confirm' => array(
					'label'       => __( 'Re-enter new password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 2,
				),
			),
		);

		/**
		 * Allows developers to register or deregister fields for the password recovery form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the password recovery form.
		 */
		$this->fields = apply_filters( 'pno_pasword_recovery_form_fields', $fields );

		// If we're on the first step. We disable the password fields temporarily.
		// If we're on the reset step, we disable the user fields.
		if ( $this->is_recovery_mode() ) {
			unset( $this->fields['user'] );
		} else {
			unset( $this->fields['password'] );
		}

		//phpcs:ignore
		if ( isset( $_GET['user_id'] ) && isset( $_GET['key'] ) && isset( $_GET['step'] ) && $_GET['step'] == 'reset' ) {
			unset( $this->fields['user'] );
		}

	}

	/**
	 * Displays the form.
	 */
	public function submit() {

		$this->init_fields();

		/**
		 * Allow developers to customize the lost password form message.
		 *
		 * @param string $message the message to display.
		 */
		$message = apply_filters( 'pno_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.' ) );

		if ( $this->is_recovery_mode() ) {

			/**
			 * Allow developers to customize the message that appears when entering a new password
			 * during the password recovery form.
			 *
			 * @param string $message
			 */
			$message = apply_filters( 'pno_change_password_message', esc_html__( 'Create a new password for the account.' ) );
		}

		posterno()->templates
			->set_template_data(
				[
					'form'         => $this,
					'action'       => $this->get_action(),
					'fields'       => $this->is_recovery_mode() ? $this->get_fields( 'password' ) : $this->get_fields( 'user' ),
					'step'         => $this->get_step(),
					'submit_label' => esc_html__( 'Reset password' ),
					'message'      => $message,
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

	/**
	 * Handles the submission of form data.
	 *
	 * @throws Exception On validation error.
	 */
	public function submit_handler() {
		try {

			if ( empty( $_POST[ 'submit_' . $this->form_name ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ "{$this->form_name}_nonce" ], "verify_{$this->form_name}_form" ) ) {
				return;
			}

			//phpcs:ignore
			if ( ! isset( $_POST[ 'pno_form' ] ) || isset( $_POST['pno_form'] ) && $_POST['pno_form'] !== $this->form_name ) {
				return;
			}

			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			// Reset the password given a valid key.
			if ( $this->is_recovery_mode() ) {

				$user_id          = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;
				$get_user         = get_user_by( 'id', $user_id );
				$verification_key = isset( $_GET['key'] ) && ! empty( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : false;

				if ( $user_id && $get_user instanceof WP_User && $verification_key ) {

					$verify_key = check_password_reset_key( $verification_key, $get_user->data->user_login );

					if ( is_wp_error( $verify_key ) ) {

						/**
						 * Allow developers to customize the error message that appears
						 * when the password reset key is invalid.
						 *
						 * @param string $message the error message.
						 * @return string the new message.
						 */
						$error_message = apply_filters( 'pno_password_recovery_key_invalid_message', esc_html__( 'The reset key is wrong or expired. Please check that you used the right reset link or request a new one.' ) );

						throw new Exception( $error_message );

					} else {

						$password_1 = $values['password']['password'];
						$password_2 = $values['password']['password_confirm'];

						if ( $password_1 !== $password_2 ) {

							$error_not_matching = esc_html__( 'Passwords do not match.' );

							/**
							 * Allow developers to customize the password recovery psw not matching message.
							 *
							 * @param string $message the error message.
							 * @return string the new message.
							 */
							$error_not_matching = apply_filters( 'pno_password_recovery_not_matching_message', $error_not_matching );

							throw new Exception( $error_not_matching );
						}

						$user_id    = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;

						wp_set_password( $password_1, $user_id );

						// Clear all user sessions.
						$sessions = WP_Session_Tokens::get_instance( $user_id );
						$sessions->destroy_all();

						$success_message = esc_html__( 'Password successfully reset.' ) . ' ' . '<a href="' . esc_url( get_permalink( pno_get_login_page_id() ) ) . '">' . esc_html__( 'Login now &raquo;' ) . '</a>';

						/**
						 * Allow developers to customize the password recovery success message.
						 *
						 * @param string $message the success message.
						 * @return string the new message.
						 */
						$success_message = apply_filters( 'pno_password_recovery_success_message', $success_message );

						$this->set_as_successful();
						$this->set_success_message( $success_message );
						$this->unbind();
						return;

					}
				} else {

					/**
					 * Allow developers to customize the password recovery invalid link message.
					 *
					 * @param string $message the error message.
					 * @return string the new message.
					 */
					$invalid_link = apply_filters( 'pno_password_recovery_invalid_link_message', esc_html__( 'The link you followed may be broken. Please check that you used the right reset link or request a new one.' ) );

					throw new Exception( $invalid_link );

				}
			} else {

				$username = $values['user']['username_email'];
				$user     = false;

				if ( is_email( $username ) && ! email_exists( $username ) || ! is_email( $username ) && ! username_exists( $username ) ) {

					/**
					 * Allow developers to customize the password recovery user not found message.
					 *
					 * @param string $message the error message.
					 * @return string the new message.
					 */
					$user_not_found_message = apply_filters( 'pno_password_recovery_invalid_user', esc_html__( 'A user with this username or email does not exist. Please check your entry and try again.' ) );

					throw new Exception( $user_not_found_message );
				}

				// Retrieve the user from the DB.
				if ( is_email( $username ) ) {
					$user = get_user_by( 'email', $username );
				} else {
					$user = get_user_by( 'login', $username );
				}

				if ( $user instanceof WP_User ) {

					// Generate a new password reset key for the selected user.
					$password_reset_key = get_password_reset_key( $user );

					// Now send an email to the user.
					if ( $password_reset_key ) {

						$subject = pno_get_option( 'password_recovery_subject' );
						$message = pno_get_option( 'password_recovery_content' );
						$heading = pno_get_option( 'password_recovery_heading' );

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
						$success_message = sprintf( esc_html__( 'We\'ve sent an email to %s with password reset instructions.' ), '<strong>' . $masked_email . '</strong>' );

						/**
						 * Allow developers to customize the success message for the password
						 * recovery form when the recovery email has been sent.
						 *
						 * @param string $success_message the message.
						 * @return string the new message.
						 */
						$success_message = apply_filters( 'pno_password_recovery_success_mail_sent', $success_message );

						$this->set_as_successful();
						$this->set_success_message( $success_message );
						$this->unbind();
						return;
					}
				} else {
					throw new Exception( esc_html__( 'Something went wrong.' ) );
				}
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Determine if we've got a key and user id to display the password fields and update the password.
	 *
	 * @return boolean
	 */
	private function is_recovery_mode() {
		return isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) && isset( $_GET['key'] ) && ! empty( $_GET['key'] ) ? true : false;
	}

}
