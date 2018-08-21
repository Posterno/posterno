<?php
/**
 * Handles the account cancellation form processing.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class PNO_Form_Account_Delete extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'account-delete';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Account_Delete The single instance of the class
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
				'name'     => esc_html__( 'Delete your account' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
		);

		/**
		 * List of steps for the account delete form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the account delete form.
		 */
		$this->steps = (array) apply_filters( 'pno_account_delete_form_steps', $steps );

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
			'account-delete' => array(
				'password_current' => array(
					'label'       => esc_html__( 'Current password' ),
					'description' => esc_html__( 'Enter your current password to confirm cancellation of your account.' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 0,
				),
			),
		);

		/**
		 * Allows developers to register or deregister fields for the account cancellation form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the acco
		 *  form.
		 */
		$this->fields = apply_filters( 'pno_account_delete_form_fields', $fields );

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
			'fields'       => $this->get_fields( 'account-delete' ),
			'step'         => $this->get_step(),
			'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
			'submit_label' => esc_html__( 'Delete account' ),
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

			if ( empty( $_POST['submit_account-delete'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['account-delete_nonce'], 'verify_account-delete_form' ) ) {
				return;
			}

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			$user = wp_get_current_user();

			if ( $user instanceof WP_User && wp_check_password( $values['account-delete']['password_current'], $user->data->user_pass, $user->ID ) && is_user_logged_in() ) {

				wp_logout();

				require_once( ABSPATH . 'wp-admin/includes/user.php' );

				wp_delete_user( $user->ID );

				$redirect_to = pno_get_option( 'cancellation_redirect' );

				if ( is_array( $redirect_to ) && isset( $redirect_to['value'] ) && ! empty( $redirect_to['value'] ) ) {
					wp_safe_redirect( get_permalink( $redirect_to['value'] ) );
					exit;
				} else {
					wp_safe_redirect( home_url() );
					exit;
				}
			} else {
				throw new Exception( __( 'The password you entered is incorrect. Your account has not been deleted.' ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

}
