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
 * The class that handles the login form.
 */
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

		$this->steps = (array) apply_filters(
			'pno_login_steps',
			array(
				'submit' => array(
					'name'     => __( 'Submit Details' ),
					'view'     => array( $this, 'submit' ),
					'handler'  => array( $this, 'submit_handler' ),
					'priority' => 10,
				),
				'done'   => array(
					'name'     => __( 'Done' ),
					'view'     => array( $this, 'done' ),
					'priority' => 30,
				),
			)
		);

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
			'login' => array(
				'title' => array(
					'label'       => __( 'Title' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1,
				),
			),
		);

		/**
		 * Allow developers to customize the login form fields.
		 *
		 * @param array $fields list of fields defined for the login form.
		 * @return array
		 */
		$this->fields = apply_filters( 'pno_login_form_fields', $fields );

	}

	/**
	 * Displays the form.
	 */
	public function submit() {

		$this->init_fields();

		posterno()->templates
			->set_template_data(
				[
					'form'   => $this->form_name,
					'action' => $this->get_action(),
					'fields' => $this->get_fields( 'login' ),
					'step'   => $this->get_step(),
				]
			)
			->get_template_part( 'form' );

		$action_links = [
			'register_link' => pno_get_option( 'login_show_registration_link' ),
			'psw_link'      => pno_get_option( 'login_show_password_link' ),
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

			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			if ( empty( $_POST['submit_job'] ) ) {
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
	 * Displays the final screen after form has been submitted.
	 */
	public function done() {

	}
}
