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

		$steps = array(
			'submit' => array(
				'name'     => esc_html__( 'Password recovery details request' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10
			),
			'sent' => array(
				'name'     => esc_html__( 'Instructions sent' ),
				'view'     => array( $this, 'instructions_sent' ),
				'handler'  => false,
				'priority' => 11
			),
			'reset' => array(
				'name'     => esc_html__( 'Reset password' ),
				'view'     => array( $this, 'reset' ),
				'handler'  => array( $this, 'reset_handler' ),
				'priority' => 12
			),
			'done' => array(
				'name'     => esc_html__( 'Done' ),
				'view'     => array( $this, 'done' ),
				'handler'  => false,
				'priority' => 13
			)
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
	 * Defines the fields of the password recovery form.
	 *
	 * @return void
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$fields = array(
			'user' => array(
				'username_email' => array(
					'label'       => __( 'Username or email' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1
				),
			),
			'password' => array(
				'password' => array(
					'label'       => __( 'New password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1
				),
				'password_2' => array(
					'label'       => __( 'Re-enter new password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 2
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

			if ( empty( $_POST['submit_login'] ) ) {
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

}
