<?php
/**
 * Handles the account data erasure request form processing.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class PNO_Form_Data_Erasure extends PNO_Form {

	/**
	 * The currently logged in user.
	 *
	 * @var object
	 */
	private $user;

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'data-erasure';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Data_Erasure The single instance of the class
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

		if ( ! is_user_logged_in() ) {
			return;
		}

		$this->user = wp_get_current_user();

		add_action( 'wp', array( $this, 'process' ) );

		$steps = array(
			'submit'           => array(
				'name'     => esc_html__( 'Request cancellation of your data' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
			'submit_confirmed' => array(
				'name'     => __( 'Data cancellation request confirmation' ),
				'view'     => array( $this, 'confirmation' ),
				'handler'  => false,
				'priority' => 11,
			),
		);

		/**
		 * List of steps for the data erasure request form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the data erasure request form.
		 */
		$this->steps = (array) apply_filters( 'pno_data_erasure_request_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( $_POST['step'], array_keys( $this->steps ) );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( $_GET['step'], array_keys( $this->steps ) );
		}

	}

	/**
	 * Defines the fields of the data request.
	 *
	 * @return void
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$fields = array(
			'data-erasure' => array(
				'password_current' => array(
					'label'       => esc_html__( 'Current password' ),
					'description' => esc_html__( 'Enter your current password to confim erasure of your personal data.' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 0,
				),
			),
		);

		/**
		 * Allows developers to register or deregister fields for the data request form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the form.
		 */
		$this->fields = apply_filters( 'pno_data_erasure_request_form_fields', $fields );

	}

	/**
	 * Handles the display of the login form.
	 *
	 * @return void
	 */
	public function submit() {

		$this->init_fields();

		$message = apply_filters(
			'pno_data_cancellation_request_form_message',
			sprintf(
				__( 'You can request cancellation of the data that we have about you. You’ll get an email sent to %s with a link to confirm your request.' ),
				'<strong>' . antispambot( $this->user->data->user_email ) . '</strong>'
			)
		);

		$data = [
			'form'         => $this->form_name,
			'action'       => $this->get_action(),
			'fields'       => $this->get_fields( 'data-erasure' ),
			'step'         => $this->get_step(),
			'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
			'message'      => $message,
			'submit_label' => esc_html__( 'Request data cancellation' ),
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

			if ( empty( $_POST['submit_data-erasure'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['data-erasure_nonce'], 'verify_data-erasure_form' ) ) {
				return;
			}

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			$submitted_password = $values['data-erasure']['password_current'];

			if ( $this->user instanceof WP_User && wp_check_password( $submitted_password, $this->user->data->user_pass, $this->user->ID ) && is_user_logged_in() ) {
				$request_id = wp_create_user_request( $this->user->data->user_email, 'remove_personal_data' );
				if ( is_wp_error( $request_id ) ) {
					throw new Exception( $request_id->get_error_message() );
				} else {
					wp_send_user_request( $request_id );
					$this->step ++;
				}
			} else {
				throw new Exception( __( 'The password you entered is incorrect.' ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Display confirmation message.
	 *
	 * @return void
	 */
	public function confirmation() {

		$message = sprintf( esc_html__( 'A confirmation email has been sent to %s. Click the link within the email to confirm your export request.' ), '<strong>' . $this->user->data->user_email . '</strong>' );

		echo '<h2>' . $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'] . '</h2>';

		$data = [
			'type'    => 'success',
			'message' => apply_filters( 'pno_personal_data_request_success_message', $message ),
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'message' );
	}

}
