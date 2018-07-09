<?php
/**
 * Handles the registration form processing.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class PNO_Form_Registration extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'registration';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Registration The single instance of the class
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

		add_filter( 'pno_form_validate_fields', [ $this, 'validate_password' ], 10, 4 );
		add_filter( 'pno_form_validate_fields', [ $this, 'validate_honeypot' ], 10, 4 );

		$steps = array(
			'submit'       => array(
				'name'     => false,
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
			'confirmation' => array(
				'name'     => false,
				'view'     => pno_get_registration_redirect() ? false : array( $this, 'confirmation_message' ),
				'handler'  => pno_get_registration_redirect() ? array( $this, 'confirmation_redirect' ) : false,
				'priority' => 20,
			),
		);

		/**
		 * List of steps for the registration form.
		 *
		 * By default there are 2 steps: the first is used to enter the details of the new account
		 * and sign him up, the second is used to redirect the new user.
		 *
		 * Each step has a "view" method that handles what's displayed on the frontend and an "handler"
		 * method that processes what's submitted through the fields within the "view" step.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the registration form.
		 */
		$this->steps = (array) apply_filters( 'pno_registration_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( $_POST['step'], array_keys( $this->steps ) );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( $_GET['step'], array_keys( $this->steps ) );
		}

	}

	/**
	 * Make sure the password is a strong one.
	 *
	 * @param boolean $pass
	 * @param array $fields
	 * @param array $values
	 * @param string $form
	 * @return mixed
	 */
	public function validate_password( $pass, $fields, $values, $form ) {
		if ( $form == $this->form_name && isset( $values['registration']['password'] ) && pno_get_option( 'strong_passwords' ) ) {

			$password_1      = $values['registration']['password'];
			$containsLetter  = preg_match( '/[A-Z]/', $password_1 );
			$containsDigit   = preg_match( '/\d/', $password_1 );
			$containsSpecial = preg_match( '/[^a-zA-Z\d]/', $password_1 );

			if ( ! $containsLetter || ! $containsDigit || ! $containsSpecial || strlen( $password_1 ) < 8 ) {
				return new WP_Error( 'password-validation-error', esc_html__( 'Password must be at least 8 characters long and contain at least 1 number, 1 uppercase letter and 1 special character.' ) );
			}
		}
		return $pass;
	}

	/**
	 * Validate the honeypot field.
	 *
	 * @param boolean $pass
	 * @param array $fields
	 * @param array $values
	 * @param string $form
	 * @return mixed
	 */
	public function validate_honeypot( $pass, $fields, $values, $form ) {
		if ( $form == $this->form_name && isset( $values['registration']['robo'] ) ) {
			if ( ! empty( $values['registration']['robo'] ) ) {
				return new WP_Error( 'honeypot-validation-error', esc_html__( 'Failed honeypot validation.' ) );
			}
		}
		return $pass;
	}

	/**
	 * Defines the fields of the login form.
	 *
	 * @return void
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$this->fields = pno_get_registration_fields();

	}

	/**
	 * Handles the display of the login form.
	 *
	 * @return void
	 */
	public function submit() {

		$this->init_fields();

		$data = [
			'form'         => $this->form_name,
			'action'       => $this->get_action(),
			'fields'       => $this->get_fields( 'registration' ),
			'step'         => $this->get_step(),
			'submit_label' => esc_html__( 'Register' ),
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'forms/general', 'form' );

	}

	/**
	 * Process the registration.
	 *
	 * @return void
	 */
	public function submit_handler() {

		try {

			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			if ( empty( $_POST['submit_registration'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['registration_nonce'], 'verify_registration_form' ) ) {
				return;
			}

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			$email_address = $values['registration']['email'];

			// Verify if a username has been submitted.
			// If no username has been supplied, use the email address.
			$has_username = isset( $values['registration']['username'] ) && ! empty( $values['registration']['username'] ) ? true : false;
			$username     = $email_address;

			if ( $has_username ) {
				$username = $values['registration']['username'];
			}

			// Verify if a password has been submitted.
			// If no password has been supplied, generate a random one.
			$has_password = isset( $values['registration']['password'] ) && ! empty( $values['registration']['password'] ) ? true : false;
			$password     = wp_generate_password( 24, true, true );

			if ( $has_password ) {
				$password = $values['registration']['password'];
			}

			$new_user_id = wp_create_user( $username, $password, $email_address );

			if ( is_wp_error( $new_user_id ) ) {
				throw new Exception( $new_user_id->get_error_message() );
			}

			// Allow developers to extend signup process.
			do_action( 'pno_before_registration_end', $new_user_id, $values );

			// Allow developers to extend signup process.
			do_action( 'pno_after_registration', $new_user_id, $values );

			// Successful, show next step.
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}

	}

	/**
	 * Displays a confirmation message after successful registration.
	 *
	 * @return void
	 */
	public function confirmation_message() {

		$success_message = apply_filters( 'wpum_registration_success_message', esc_html__( 'Registration complete. We have sent you a confirmation email with your details.' ) );

		$data = [
			'message' => $success_message,
			'type'    => 'success',
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'message' );

	}

	/**
	 * Redirect the user to another page after successful registration.
	 *
	 * @return void
	 */
	public function confirmation_redirect() {

		if ( pno_get_registration_redirect() ) {
			wp_safe_redirect( pno_get_registration_redirect() );
			exit;
		}

	}

}
