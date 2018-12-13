<?php
/**
 * Handles display and processing of the registration form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles the form.
 */
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

		if ( pno_get_option( 'enable_role_selection' ) ) {
			add_filter( 'pno_form_validate_fields', [ $this, 'validate_role' ], 10, 4 );
		}

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
		 * @since 0.1.0
		 * @param array $steps the list of steps for the registration form.
		 */
		$this->steps = (array) apply_filters( 'pno_registration_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( intval( $_POST['step'] ), array_keys( $this->steps ), true );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( intval( $_GET['step'] ), array_keys( $this->steps ), true );
		}

	}

	/**
	 * Make sure the password is a strong one.
	 *
	 * @param boolean $pass pass validation or not.
	 * @param array   $fields all fields belonging to the form.
	 * @param array   $values values sent through the form.
	 * @param string  $form form's name.
	 * @return mixed
	 */
	public function validate_password( $pass, $fields, $values, $form ) {
		if ( $form == $this->form_name && isset( $values['registration']['password'] ) && pno_get_option( 'strong_passwords' ) ) {
			$password_1       = $values['registration']['password'];
			$contains_letter  = preg_match( '/[A-Z]/', $password_1 );
			$contains_digit   = preg_match( '/\d/', $password_1 );
			$contains_special = preg_match( '/[^a-zA-Z\d]/', $password_1 );
			if ( ! $contains_letter || ! $contains_digit || ! $contains_special || strlen( $password_1 ) < 8 ) {
				return new WP_Error( 'password-validation-error', esc_html__( 'Password must be at least 8 characters long and contain at least 1 number, 1 uppercase letter and 1 special character.' ) );
			}
		}

		if ( $form == $this->form_name && isset( $values['registration']['password'] ) && isset( $values['registration']['password_confirm'] ) ) {
			$password_1 = $values['registration']['password'];
			$password_2 = $values['registration']['password'];
			if ( $password_1 !== $password_2 ) {
				return new WP_Error( 'passwords-not-matching', esc_html__( 'Passwords do not match.' ) );
			}
		}

		return $pass;
	}

	/**
	 * Validate the honeypot field.
	 *
	 * @param boolean $pass pass validation or not.
	 * @param array   $fields all fields belonging to the form.
	 * @param array   $values values sent through the form.
	 * @param string  $form form's name.
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
	 * Validate role on submission.
	 *
	 * @param boolean $pass pass validation or not.
	 * @param array   $fields all fields belonging to the form.
	 * @param array   $values values sent through the form.
	 * @param string  $form form's name.
	 * @return mixed
	 */
	public function validate_role( $pass, $fields, $values, $form ) {
		if ( $form == $this->form_name && isset( $values['registration']['role'] ) ) {
			$role_field      = $values['registration']['role'];
			$selected_roles  = pno_get_option( 'allowed_roles' );
			$available_roles = [];
			foreach ( $selected_roles as $role ) {
				$available_roles[] = $role['value'];
			}
			if ( is_array( $available_roles ) && ! in_array( $role_field, $available_roles ) ) {
				return new WP_Error( 'role-validation-error', __( 'Select a valid role from the list.' ) );
			}
		}
		return $pass;
	}

	/**
	 * Initializes the fields used in the form.
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$fields = array(
			'registration' => pno_get_registration_fields(),
		);

		$this->fields = $fields;

	}

	/**
	 * Displays the form.
	 */
	public function submit() {

		$this->init_fields();

		posterno()->templates
			->set_template_data(
				[
					'form'         => $this,
					'action'       => $this->get_action(),
					'fields'       => $this->get_fields( 'registration' ),
					'step'         => $this->get_step(),
					'submit_label' => esc_html__( 'Register' ),
				]
			)
			->get_template_part( 'form' );

		$action_links = [
			'login_link' => pno_get_option( 'registration_show_login_link' ),
			'psw_link'   => pno_get_option( 'registration_show_password_link' ),
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

			$values = $values['registration'];

			$email_address = $values['email'];

			// Verify if a username has been submitted.
			// If no username has been supplied, use the email address.
			$has_username = isset( $values['username'] ) && ! empty( $values['username'] ) ? true : false;
			$username     = $email_address;

			if ( $has_username ) {
				$username = $values['username'];
			}

			// Verify if a password has been submitted.
			// If no password has been supplied, generate a random one.
			$has_password = isset( $values['password'] ) && ! empty( $values['password'] ) ? true : false;
			$password     = wp_generate_password( 24, true, true );

			if ( $has_password ) {
				$password = $values['password'];
			}

			/**
			 * Allow developers to extend the signup process before actually
			 * registering the new user.
			 *
			 * @param array $values all the fields submitted through the form.
			 * @param object $this the class instance managing the form.
			 */
			do_action( 'pno_before_registration', $values, $this );

			$new_user_id = wp_create_user( $username, $password, $email_address );

			if ( is_wp_error( $new_user_id ) ) {
				throw new Exception( $new_user_id->get_error_message() );
			}

			// Assign the role set into the registration form.
			if ( pno_get_option( 'allowed_roles' ) && isset( $values['role'] ) ) {
				$user = new WP_User( $new_user_id );
				$user->set_role( $values['role'] );
			}

			// Now process all other custom fields.
			foreach ( $values as $key => $value ) {
				if ( $key === 'email' || $key === 'password' || $key === 'username' ) {
					continue;
				}
				if ( pno_is_default_field( $key ) ) {
					if ( $key == 'website' ) {
						update_user_meta( $new_user_id, 'user_url', $value );
					} else {
						update_user_meta( $new_user_id, $key, $value );
					}
				} else {

					$field_type = $this->fields['registration'][ $key ]['type'];

					if ( $field_type === 'checkbox' ) {
						if ( $value === '1' ) {
							carbon_set_user_meta( $new_user_id, $key, true );
						}
					} else {
						carbon_set_user_meta( $new_user_id, $key, $value );
					}

				}
			}

			/**
			 * Allow developers to extend the signup process before firing
			 * the registration confirmation email.
			 *
			 * @param string $new_user_id the user id.
			 * @param array $values all the fields submitted through the form.
			 * @param object $this the class instance managing the form.
			 */
			do_action( 'pno_before_registration_end', $new_user_id, $values, $this );

			// Send registration confirmation emails.
			pno_send_email(
				'core_user_registration',
				$email_address,
				[
					'user_id'             => $new_user_id,
					'plain_text_password' => $password,
				]
			);

			/**
			 * Allow developers to extend the signup process after firing
			 * the registration confirmation email and before showing the
			 * success message/page.
			 *
			 * @param string $new_user_id the user id.
			 * @param array $values all the fields submitted through the form.
			 * @param object $this the class instance managing the form.
			 */
			do_action( 'pno_after_registration', $new_user_id, $values, $this );

			// Automatically log a user in if enabled.
			if ( pno_get_option( 'login_after_registration' ) ) {
				pno_log_user_in( $new_user_id );
			}

			if ( pno_get_registration_redirect() ) {
				wp_safe_redirect( pno_get_registration_redirect() );
				exit;
			} else {

				/**
				 * Allow developers to customize the message displayed after successfull registration.
				 *
				 * @param string $message the message that appears after registration.
				 */
				$success_message = apply_filters( 'pno_registration_success_message', esc_html__( 'Registration complete. We have sent you a confirmation email with your details.' ) );

				$this->set_as_successful();
				$this->set_success_message( $success_message );
				$this->unbind();
				return;
			}

			// Successful, show next step.
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

}
