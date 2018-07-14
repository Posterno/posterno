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

		$steps = array(
			'submit'  => array(
				'name'     => false,
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
			'updated' => array(
				'name'     => false,
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
				'password'        => array(
					'label'       => esc_html__( 'Password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 0,
				),
				'password_repeat' => array(
					'label'       => esc_html__( 'Repeat password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1,
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
			'submit_label' => esc_html__( 'Update password' ),
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

		$message = apply_filters( 'pno_account_updated_message', esc_html__( 'Account details successfully updated.' ) );

		$data = [
			'type'    => 'success',
			'message' => $message,
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'message' );

	}

}
