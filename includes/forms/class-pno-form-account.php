<?php
/**
 * Handles the account form processing.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class PNO_Form_Account extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'account';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Account The single instance of the class
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
				'name'     => __( 'Account settings' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
			'updated' => array(
				'name'     => __( 'Account settings' ),
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
		$this->steps = (array) apply_filters( 'pno_account_form_steps', $steps );

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
			'account' => pno_get_account_fields( get_current_user_id() ),
		);

		/**
		 * Allows developers to register or deregister fields for the login form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the login form.
		 */
		$this->fields = apply_filters( 'pno_account_form_fields', $fields );

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
			'fields'       => $this->get_fields( 'account' ),
			'step'         => $this->get_step(),
			'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
			'submit_label' => esc_html__( 'Save changes' ),
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

			if ( empty( $_POST['submit_account'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['account_nonce'], 'verify_account_form' ) ) {
				return;
			}

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			$user_id = get_current_user_id();

			$user_data = [
				'ID' => $user_id,
			];

			do_action( 'pno_before_user_update', $this, $values, $user_id );

			// Update first name and last name.
			if ( isset( $values['account']['first_name'] ) ) {
				$user_data['first_name'] = $values['account']['first_name'];
			}
			if ( isset( $values['account']['last_name'] ) ) {
				$user_data['last_name'] = $values['account']['last_name'];
			}

			// Update email address.
			if ( isset( $values['account']['email'] ) ) {
				$user_data['user_email'] = $values['account']['email'];
			}

			// Update website.
			if ( isset( $values['account']['website'] ) ) {
				$user_data['user_url'] = $values['account']['website'];
			}

			if ( isset( $values['account']['description'] ) ) {
				$user_data['description'] = $values['account']['description'];
			}

			$updated_user_id = wp_update_user( $user_data );

			if ( is_wp_error( $updated_user_id ) ) {
				throw new Exception( $updated_user_id->get_error_message() );
			}

			// Update the avatar.
			if ( pno_get_option( 'allow_avatars' ) ) {
				$currently_uploaded_file   = isset( $_POST['current_avatar'] ) && ! empty( $_POST['current_avatar'] ) ? esc_url_raw( $_POST['current_avatar'] ) : false;
				$existing_avatar_file_path = get_user_meta( $updated_user_id, 'current_user_avatar_path', true );
				if ( $currently_uploaded_file && $existing_avatar_file_path && isset( $values['account']['avatar']['url'] ) && $values['account']['avatar']['url'] !== $currently_uploaded_file ) {
					wp_delete_file( $existing_avatar_file_path );
				}
				if ( isset( $values['account']['avatar']['url'] ) && $currently_uploaded_file !== $values['account']['avatar']['url'] ) {
					carbon_set_user_meta( $updated_user_id, 'current_user_avatar', $values['account']['avatar']['url'] );
					update_user_meta( $updated_user_id, 'current_user_avatar_path', $values['account']['avatar']['path'] );
				}
				if ( ! $currently_uploaded_file && file_exists( $existing_avatar_file_path ) ) {
					wp_delete_file( $existing_avatar_file_path );
					carbon_set_user_meta( $updated_user_id, 'current_user_avatar', false );
					delete_user_meta( $updated_user_id, 'current_user_avatar_path' );
				}
			}

			// Now update the custom fields that are not marked as default profile fields.
			foreach ( $values['account'] as $key => $value ) {
				if ( ! pno_is_default_profile_field( $key ) ) {
					if ( $value == '1' ) {
						carbon_set_user_meta( $updated_user_id, $key, true );
					} elseif ( is_array( $value ) && isset( $value['url'] ) && isset( $value['path'] ) ) {
						carbon_set_user_meta( $updated_user_id, $key, $value['url'] );
						update_post_meta( $updated_user_id, $key, $value['path'] );
					} else {
						carbon_set_user_meta( $updated_user_id, $key, $value );
					}
				}
			}

			do_action( 'pno_after_user_update', $this, $values, $updated_user_id );

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
