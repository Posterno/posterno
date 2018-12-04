<?php
/**
 * Handles display and processing of the login form.
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
class PNO_Form_Password_Change extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'password-change';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Password_Change The single instance of the class
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

		$steps = array(
			'submit'  => array(
				'name'     => esc_html__( 'Change password' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
			'updated' => array(
				'name'     => esc_html__( 'Change password' ),
				'view'     => array( $this, 'updated' ),
				'handler'  => false,
				'priority' => 11,
			),
		);

		/**
		 * List of steps for the account form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the account form.
		 */
		$this->steps = (array) apply_filters( 'pno_password_change_form_steps', $steps );

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
			'password-change' => array(
				'password_current'    => array(
					'label'       => esc_html__( 'Current password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 0,
				),
				'password'        => array(
					'label'       => esc_html__( 'New password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1,
				),
				'password_confirm' => array(
					'label'       => esc_html__( 'Repeat new password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 2,
				),
			),
		);

		/**
		 * Allows developers to register or deregister fields for the password change form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the password change form.
		 */
		$this->fields = apply_filters( 'pno_password_change_form_fields', $fields );

	}

	/**
	 * Make sure the password is a strong one and matches the confirmation.
	 *
	 * @param boolean $pass pass validation or not.
	 * @param array   $fields all fields belonging to the form.
	 * @param array   $values values sent through the form.
	 * @param string  $form form's name.
	 * @return mixed
	 */
	public function validate_password( $pass, $fields, $values, $form ) {
		if ( $form == $this->form_name && isset( $values['password-change']['password'] ) && pno_get_option( 'strong_passwords' ) ) {
			$password_1       = $values['password-change']['password'];
			$contains_letter  = preg_match( '/[A-Z]/', $password_1 );
			$contains_digit   = preg_match( '/\d/', $password_1 );
			$contains_special = preg_match( '/[^a-zA-Z\d]/', $password_1 );
			if ( ! $contains_letter || ! $contains_digit || ! $contains_special || strlen( $password_1 ) < 8 ) {
				return new WP_Error( 'password-validation-error', esc_html__( 'Password must be at least 8 characters long and contain at least 1 number, 1 uppercase letter and 1 special character.' ) );
			}
		}

		if ( $form == $this->form_name && isset( $values['password-change']['password'] ) && isset( $values['password-change']['password_confirm'] ) ) {
			$password_1 = $values['password-change']['password'];
			$password_2 = $values['password-change']['password'];
			if ( $password_1 !== $password_2 ) {
				return new WP_Error( 'passwords-not-matching', esc_html__( 'Passwords do not match.' ) );
			}
		}
		return $pass;
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
					'fields'       => $this->get_fields( 'password-change' ),
					'step'         => $this->get_step(),
					'submit_label' => esc_html__( 'Change password' ),
					'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
				]
			)
			->get_template_part( 'form' );

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

			$user = wp_get_current_user();

			$submitted_password = $values['password-change']['password_current'];

			if ( $user instanceof WP_User && wp_check_password( $submitted_password, $user->data->user_pass, $user->ID ) && is_user_logged_in() ) {
				$updated_user_id = wp_update_user(
					[
						'ID'        => $user->ID,
						'user_pass' => $values['password-change']['password'],
					]
				);

				if ( is_wp_error( $updated_user_id ) ) {
					throw new Exception( $updated_user_id->get_error_message() );
				} else {

					/**
					 * Allow developers to customize the confirmation message when a user changes his password.
					 *
					 * @param string $message the message.
					 * @return string
					 */
					$message = apply_filters( 'pno_password_change_confirmation_message', esc_html__( 'Password successfully updated.' ) );

					$this->unbind();
					$this->set_as_successful();
					$this->set_success_message( $message );
					return;
				}
			} else {
				throw new Exception( __( 'The password you entered is incorrect.' ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

}
