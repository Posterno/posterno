<?php
/**
 * Handles the password recovery form processing.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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

		add_filter( 'pno_form_validate_fields', [ $this, 'validate_username_or_email' ], 10, 4 );
		add_filter( 'pno_form_validate_fields', [ $this, 'validate_passwords' ], 10, 4 );

		$steps = array(
			'submit' => array(
				'name'     => esc_html__( 'Password recovery details request' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
			'sent'   => array(
				'name'     => esc_html__( 'Instructions sent' ),
				'view'     => array( $this, 'instructions_sent' ),
				'handler'  => false,
				'priority' => 11,
			),
			'reset'  => array(
				'name'     => esc_html__( 'Reset password' ),
				'view'     => array( $this, 'reset' ),
				'handler'  => array( $this, 'reset_handler' ),
				'priority' => 12,
			),
			'done'   => array(
				'name'     => esc_html__( 'Done' ),
				'view'     => array( $this, 'done' ),
				'handler'  => false,
				'priority' => 13,
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
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( $_POST['step'], array_keys( $this->steps ) );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( $_GET['step'], array_keys( $this->steps ) );
		}

	}

	/**
	 * Validate the requested username or email.
	 *
	 * @param boolean $pass
	 * @param array $fields
	 * @param array $values
	 * @param string $form
	 * @return void
	 */
	public function validate_username_or_email( $pass, $fields, $values, $form ) {
		if ( $form == 'password-recovery' && isset( $values['user']['username_email'] ) ) {
			$username = sanitize_text_field( $values['user']['username_email'] );
			if ( is_email( $username ) && ! email_exists( $username ) || ! is_email( $username ) && ! username_exists( $username ) ) {
				return new WP_Error( 'username-validation-error', esc_html__( 'A user with this username or email does not exist. Please check your entry and try again.' ) );
			}
		}
		return $pass;
	}

	/**
	 * Validate the 2 passwords are the same and make sure they're safe enough.
	 *
	 * @param boolean $pass
	 * @param array $fields
	 * @param array $values
	 * @param string $form
	 * @return void
	 */
	public function validate_passwords( $pass, $fields, $values, $form ) {
		if ( $form == 'password-recovery' && isset( $values['password']['password'] ) && isset( $values['password']['password_2'] ) ) {
			$password_1 = $values['password']['password'];
			$password_2 = $values['password']['password_2'];
			if ( $password_1 !== $password_2 ) {
				return new WP_Error( 'password-validation-nomatch', esc_html__( 'Error: passwords do not match.' ) );
			}

			if ( pno_get_option( 'strong_passwords' ) ) {
				$containsLetter  = preg_match( '/[A-Z]/', $password_1 );
				$containsDigit   = preg_match( '/\d/', $password_1 );
				$containsSpecial = preg_match( '/[^a-zA-Z\d]/', $password_1 );
				if ( ! $containsLetter || ! $containsDigit || ! $containsSpecial || strlen( $password_1 ) < 8 ) {
					return new WP_Error( 'password-validation-error', esc_html__( 'Password must be at least 8 characters long and contain at least 1 number and 1 uppercase letter and 1 special character.' ) );
				}
			}
		}
		return $pass;
	}

	/**
	 * Defines the fields of the password recovery form.
	 *
	 * @return void
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
				'password_2' => array(
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
		if ( $this->get_step_key() == 'submit' ) {
			unset( $this->fields['password'] );
		} elseif ( $this->get_step_key() == 'reset' ) {
			unset( $this->fields['user'] );
		}
		if ( isset( $_GET['user_id'] ) && isset( $_GET['key'] ) && isset( $_GET['step'] ) && $_GET['step'] == 'reset' ) {
			unset( $this->fields['user'] );
		}

	}

	/**
	 * Handles the display of the password recovery.
	 *
	 * @return void
	 */
	public function submit() {

		$this->init_fields();

		$data = [
			'form'         => $this->form_name,
			'action'       => $this->get_action(),
			'fields'       => $this->get_fields( 'user' ),
			'step'         => $this->get_step(),
			'submit_label' => esc_html__( 'Reset password' ),
			'message'      => apply_filters( 'pno_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.' ) ),
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'forms/general', 'form' );

	}

	/**
	 * Handles verification of the submitted login details but
	 * does not actually log the user in.
	 *
	 * @return void
	 */
	public function submit_handler() {
		try {
			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			if ( empty( $_POST['submit_password-recovery'] ) ) {
				return;
			}

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			$username = $values['user']['username_email'];
			$user     = false;

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

					posterno()->emails->__set( 'user_id', $user->data->ID );
					if ( $heading ) {
						posterno()->emails->__set( 'heading', $heading );
					}
					posterno()->emails->__set( 'password_reset_key', $password_reset_key );
					posterno()->emails->send( $user->data->user_email, $subject, $message );

				}
			} else {
				throw new Exception( esc_html__( 'Something went wrong.' ) );
			}

			// Successful, show next step.
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Display a success message after successfully requesting a new password.
	 *
	 * @return void
	 */
	public function instructions_sent() {

		$values = $this->get_posted_fields();

		$username = $values['user']['username_email'];

		if ( is_email( $username ) ) {
			$user = get_user_by( 'email', $username );
		} else {
			$user = get_user_by( 'login', $username );
		}

		$data = [
			'email' => $user->data->user_email,
			'from'  => pno_get_option( 'from_email' ),
		];
		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'messages/password-reset', 'request-success' );
	}

	/**
	 * Display the password reset form.
	 *
	 * @return void
	 */
	public function reset() {

		$this->init_fields();

		// Grab all the details form the URL first.
		$user_id          = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;
		$get_user         = get_user_by( 'id', $user_id );
		$verification_key = isset( $_GET['key'] ) && ! empty( $_GET['key'] ) ? $_GET['key'] : false;

		// Verify the url is properly formatted and has correct information.
		if ( $user_id && $get_user instanceof WP_User && $verification_key ) {
			$verify_key = check_password_reset_key( $verification_key, $get_user->data->user_login );
			if ( is_wp_error( $verify_key ) ) {
				$data = [
					'message' => esc_html__( 'The reset key is wrong or expired. Please check that you used the right reset link or request a new one.' ),
				];
				posterno()->templates
					->set_template_data( $data )
					->get_template_part( 'message' );
			} else {
				$data = [
					'form'         => $this->form_name,
					'action'       => $this->get_action(),
					'fields'       => $this->get_fields( 'password' ),
					'step'         => $this->get_step(),
					'message'      => apply_filters( 'pno_new_password_message', esc_html__( 'Enter a new password below.' ) ),
					'submit_label' => esc_html__( 'Reset password' ),
				];
				posterno()->templates
					->set_template_data( $data )
					->get_template_part( 'forms/general', 'form' );
			}
		} else {
			$data = [
				'type' => 'error',
				'message' => esc_html__( 'The link you followed may be broken. Please check that you used the right reset link or request a new one.' ),
			];
			posterno()->templates
				->set_template_data( $data )
				->get_template_part( 'message' );
		}
	}

	/**
	 * Finally reset the user password now.
	 *
	 * @return void
	 */
	public function reset_handler() {
		try {

			$this->init_fields();

			$values = $this->get_posted_fields();

			if ( ! wp_verify_nonce( $_POST['password-recovery_nonce'], 'verify_password-recovery_form' ) ) {
				return;
			}

			if ( empty( $_POST['submit_password-recovery'] ) ) {
				return;
			}

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			$password_1 = $values['password']['password'];
			$password_2 = $values['password']['password_2'];
			$user_id    = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;

			wp_set_password( $password_1, $user_id );

			// Clear all user sessions.
			$sessions = WP_Session_Tokens::get_instance( $user_id );
			$sessions->destroy_all();

			// Successful, show next step.
			$this->step ++;
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Display success message at the end.
	 *
	 * @return void
	 */
	public function done() {
		$data = [
			'type'    => 'success',
			'message' => esc_html__( 'Password successfully reset.' ) . ' ' . '<a href="' . get_permalink( pno_get_login_page_id() ) . '">' . esc_html__( 'Login now &raquo;' ) . '</a>',
		];
		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'message' );
	}

}
