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
 * The class that handles the login form.
 */
class PNO_Form_Registration extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'registration';

	/**
	 * Holds the ID of the user that is logging in.
	 *
	 * @var boolean|string|int
	 */
	public $user_id = false;

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
					'form'         => $this->form_name,
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

			// Successful, show next step.
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

}
