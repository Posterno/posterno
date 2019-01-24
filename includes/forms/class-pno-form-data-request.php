<?php
/**
 * Handles display and processing of the data requests form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use PNO\Exception;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles the form.
 */
class PNO_Form_Data_Request extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'data-request';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Data_Request The single instance of the class
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
			'submit'           => array(
				'name'     => esc_html__( 'Download your data' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			)
		);

		/**
		 * List of steps for the data request form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the data request form.
		 */
		$this->steps = (array) apply_filters( 'pno_data_request_form_steps', $steps );

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
			'data-request' => array(
				'current_password' => array(
					'label'       => esc_html__( 'Current password' ),
					'description' => esc_html__( 'Enter your current password to confim export of your personal data.' ),
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
		$this->fields = apply_filters( 'pno_data_request_form_fields', $fields );

	}

	/**
	 * Displays the form.
	 */
	public function submit() {

		$this->init_fields();

		$user = wp_get_current_user();

		$message = apply_filters(
			'pno_data_request_form_message',
			sprintf(
				__( 'You can request a file with the information that we believe is most relevant and useful to you. You’ll get an email sent to %s with a link when it’s ready to be downloaded.' ),
				'<strong>' . antispambot( $user->data->user_email ) . '</strong>'
			)
		);

		$data = [
			'form'         => $this,
			'action'       => $this->get_action(),
			'fields'       => $this->get_fields( 'data-request' ),
			'step'         => $this->get_step(),
			'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
			'message'      => $message,
			'submit_label' => esc_html__( 'Request data' ),
		];

		posterno()->templates
			->set_template_data( $data )
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
				throw new Exception( $validation_status->get_error_message(), $validation_status->get_error_code() );
			}

			$user = wp_get_current_user();

			$submitted_password = $values['data-request']['current_password'];

			if ( $user instanceof WP_User && wp_check_password( $submitted_password, $user->data->user_pass, $user->ID ) && is_user_logged_in() ) {

				$request_id = wp_create_user_request( $user->user_email, 'export_personal_data' );

				if ( is_wp_error( $request_id ) ) {
					throw new Exception( $request_id->get_error_message(), $request_id->get_error_code() );
				} else {
					wp_send_user_request( $request_id );

					$message = sprintf( esc_html__( 'A confirmation email has been sent to %s. Click the link within the email to confirm your export request.' ), '<strong>' . $user->data->user_email . '</strong>' );

					/**
					 * Allow developers to customize the data request form success message.
					 *
					 * @param string $message the success message.
					 * @return string the new message.
					 */
					$message = apply_filters( 'pno_personal_data_request_success_message', $message );

					$this->unbind();
					$this->set_as_successful();
					$this->set_success_message( $message );
					return;

				}
			} else {
				throw new Exception( __( 'The password you entered is incorrect.' ) );
			}

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
