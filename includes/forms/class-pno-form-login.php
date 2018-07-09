<?php
/**
 * Handles the login form processing.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class PNO_Form_Login extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'login';

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
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'process' ) );

		$steps = array(
			'submit' => array(
				'name'     => __( 'Login Details' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
			'login'  => array(
				'name'     => false,
				'view'     => false,
				'handler'  => array( $this, 'login_handler' ),
				'priority' => 20,
			),
		);

		/**
		 * List of steps for the login form.
		 *
		 * By default there are 2 steps: the first is used to validate the user's account,
		 * the second is used to actually log the user in.
		 *
		 * Each step has a "view" method that handles what's displayed on the frontend and an "handler"
		 * method that processes what's submitted through the fields within the "view" step.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the login form.
		 */
		$this->steps = (array) apply_filters( 'pno_login_form_steps', $steps );

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
			'login' => array(
				'username' => array(
					'label'       => pno_get_login_label(),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1,
				),
				'password' => array(
					'label'       => __( 'Password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 2,
				),
				'remember' => array(
					'label'    => __( 'Remember me' ),
					'type'     => 'checkbox',
					'required' => false,
					'priority' => 3,
				),
			),
		);

		/**
		 * Allows developers to register or deregister fields for the login form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the login form.
		 */
		$this->fields = apply_filters( 'pno_login_form_fields', $fields );

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
			'fields'       => $this->get_fields( 'login' ),
			'step'         => $this->get_step(),
			'submit_label' => esc_html__( 'Login' ),
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

			$username = $values['login']['username'];
			$password = $values['login']['password'];

			$authenticate = wp_authenticate( $username, $password );

			if ( is_wp_error( $authenticate ) ) {

				throw new Exception( $authenticate->get_error_message() );

			} elseif ( $authenticate instanceof WP_User ) {

				$this->user_id = $authenticate->data->ID;

			}

			// Successful, show next step.
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Log the user in after his credentials have been verified.
	 *
	 * @return void
	 */
	public function login_handler() {

		try {
			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values   = $this->get_posted_fields();
			$username = $values['login']['username'];
			$password = $values['login']['password'];
			$creds    = [
				'user_login'    => $username,
				'user_password' => $password,
				'remember'      => $values['login']['remember'] ? true : false,
			];

			$user = wp_signon( $creds );

			if ( is_wp_error( $user ) ) {
				throw new Exception( $user->get_error_message() );
			} else {
				wp_safe_redirect( pno_get_login_redirect() );
				exit;
			}

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}

	}

}
