<?php
/**
 * Handles the password change form processing.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class PNO_Form_Password extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'password';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Password The single instance of the class
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
		$this->steps = (array) apply_filters( 'pno_password_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( $_POST['step'], array_keys( $this->steps ) );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( $_GET['step'], array_keys( $this->steps ) );
		}

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

		$fields = array(
			'password' => array(
				'password_current'    => array(
					'label'       => esc_html__( 'Current password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 0,
				),
				'password_new'        => array(
					'label'       => esc_html__( 'New password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1,
				),
				'password_new_repeat' => array(
					'label'       => esc_html__( 'Repeat new password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 2,
				),
			),
		);

		/**
		 * Allows developers to register or deregister fields for the login form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the login form.
		 */
		$this->fields = apply_filters( 'pno_password_form_fields', $fields );

	}

	/**
	 * Make sure the password is a strong one and matches the confirmation.
	 *
	 * @param boolean $pass
	 * @param array $fields
	 * @param array $values
	 * @param string $form
	 * @return mixed
	 */
	public function validate_password( $pass, $fields, $values, $form ) {
		if ( $form == $this->form_name && isset( $values['password']['password_new'] ) ) {

			$password_1      = $values['password']['password_new'];
			$password_2      = $values['password']['password_new_repeat'];

			if ( pno_get_option( 'strong_passwords' ) ) {
				$containsLetter  = preg_match( '/[A-Z]/', $password_1 );
				$containsDigit   = preg_match( '/\d/', $password_1 );
				$containsSpecial = preg_match( '/[^a-zA-Z\d]/', $password_1 );

				if ( ! $containsLetter || ! $containsDigit || ! $containsSpecial || strlen( $password_1 ) < 8 ) {
					return new WP_Error( 'password-validation-error', esc_html__( 'Password must be at least 8 characters long and contain at least 1 number, 1 uppercase letter and 1 special character.' ) );
				}
			}

			if ( $password_1 !== $password_2 ) {
				return new WP_Error( 'password-validation-nomatch', esc_html__( 'Error: passwords do not match.' ) );
			}
		}
		return $pass;
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
			'fields'       => $this->get_fields( 'password' ),
			'step'         => $this->get_step(),
			'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
			'submit_label' => esc_html__( 'Change password' ),
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

			if ( empty( $_POST['submit_password'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['password_nonce'], 'verify_password_form' ) ) {
				return;
			}

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			$user = wp_get_current_user();

			$submitted_password = $values['password']['password_current'];

			if ( $user instanceof WP_User && wp_check_password( $submitted_password, $user->data->user_pass, $user->ID ) && is_user_logged_in() ) {

				$updated_user_id = wp_update_user(
					[
						'ID'        => $user->ID,
						'user_pass' => $values['password']['password_new'],
					]
				);

				if ( is_wp_error( $updated_user_id ) ) {
					throw new Exception( $updated_user_id->get_error_message() );
				}
			} else {
				throw new Exception( __( 'The password you entered is incorrect.' ) );
			}

			// Successful, show next step.
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Display a success message after details have been updated.
	 *
	 * @return void
	 */
	public function updated() {

		$message = apply_filters( 'pno_account_updated_message', esc_html__( 'Password successfully updated.' ) );

		$data = [
			'type'    => 'success',
			'message' => $message,
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'message' );

	}

}
