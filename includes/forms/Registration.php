<?php
/**
 * Handles display and processing of the registration form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018 - 2019, Sematico, LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Forms;

use PNO\Form\Form;
use PNO\Validator;
use PNO\Exception;
use PNO\Form\DefaultSanitizer;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class of the registration form.
 */
class Registration {

	use DefaultSanitizer;

	/**
	 * The form object containing all the details about the form.
	 *
	 * @var Form
	 */
	public $form;

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'registration';

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
	 * Get things started.
	 */
	public function __construct() {
		$this->form = Form::createFromConfig( $this->getFields() );
		$this->addSanitizer( $this->form );
		$this->init();
	}

	/**
	 * Get things started.
	 *
	 * @return void
	 */
	public function init() {
		$this->hook();
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
	}

	/**
	 * Get fields for the form.
	 *
	 * @return array
	 */
	protected function getFields() {

		$necessaryFields = [
			/**
			 * Honeypot field.
			 */
			'hp-comments' => [
				'type'       => 'text',
				'label'      => esc_html__( 'If you\'re human leave this blank:', 'posterno' ),
				'validators' => new Validator\BeEmpty(),
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 99,
			],
			'submit-form' => [
				'type'       => 'button',
				'value'      => esc_html__( 'Register', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 999,
			],
		];

		$fields = array_merge( pno_get_registration_fields(), $necessaryFields );

		/**
		 * Filter: allows customization of the fields for the registration form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_registration_form_fields', $fields );

		uasort( $fields, 'pno_sort_array_by_priority' );

		return $fields;

	}

	/**
	 * Render the form.
	 *
	 * @return void
	 */
	public function render() {

		if ( is_user_logged_in() ) {

			$data = [
				'user' => wp_get_current_user(),
			];

			posterno()->templates
				->set_template_data( $data )
				->get_template_part( 'logged-user' );

		} else {

			$this->form->filterValues();
			$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
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

	}

	/**
	 * Process the form.
	 *
	 * @throws Exception When there's an error during credentials process.
	 * @return void
	 */
	public function process() {
		try {

			//phpcs:ignore
			if ( ! isset( $_POST[ 'pno_form' ] ) || isset( $_POST['pno_form'] ) && $_POST['pno_form'] !== $this->form_name ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ "{$this->form_name}_nonce" ], "verify_{$this->form_name}_form" ) ) {
				return;
			}

			$this->form->setFieldValues( $_POST );

			if ( $this->form->isValid() ) {

				$email_address = $this->form->getFieldValue( 'email' );

				// Verify if a username has been submitted.
				// If no username has been supplied, use the email address.
				$has_username = ! empty( $this->form->getFieldValue( 'username' ) ) ? true : false;
				$username     = $email_address;

				if ( $has_username ) {
					$username = $this->form->getFieldValue( 'username' );
				}

				if ( pno_get_option( 'verify_password' ) && ! pno_get_option( 'disable_password' ) ) {
					if ( $this->form->getFieldValue( 'password' ) !== $this->form->getFieldValue( 'password_confirm' ) ) {
						throw new Exception( esc_html__( 'Passwords do not match.', 'posterno' ) );
					}
				}

				// Verify if a password has been submitted.
				// If no password has been supplied, generate a random one.
				$has_password = ! empty( $this->form->getFieldValue( 'password' ) ) ? true : false;
				$password     = wp_generate_password( 24, true, true );

				if ( $has_password ) {
					$password = $this->form->getFieldValue( 'password' );
				}

				/**
				 * Allow developers to extend the signup process before actually
				 * registering the new user.
				 *
				 * @param Form $form the form object.
				 */
				do_action( 'pno_before_registration', $this->form );

				$new_user_id = wp_create_user( $username, $password, $email_address );

				if ( is_wp_error( $new_user_id ) ) {
					throw new Exception( $new_user_id->get_error_message(), $new_user_id->get_error_code() );
				}

				// Assign the role set into the registration form.
				if ( pno_get_option( 'allowed_roles' ) && $this->form->getFieldValue( 'role' ) && array_key_exists( $this->form->getFieldValue( 'role' ), pno_get_allowed_user_roles() ) && count( pno_get_allowed_user_roles() ) >= 2 ) {
					$user = new \WP_User( $new_user_id );
					$user->set_role( $this->form->getFieldValue( 'role' ) );
				}

				// Now process all other custom fields.
				foreach ( $this->form->toArray() as $key => $value ) {
					if ( in_array( $key, $this->fieldsToSkip() ) ) {
						continue;
					}
					if ( pno_is_default_field( $key ) ) {
						if ( $key == 'website' ) {
							update_user_meta( $new_user_id, 'user_url', $value );
						} else {
							update_user_meta( $new_user_id, $key, $value );
						}
					} else {

						$field      = $this->form->getField( $key );
						$field_type = $field->getType();

						if ( $field_type === 'checkbox' ) {
							if ( $value === true || $value === '1' ) {
								carbon_set_user_meta( $new_user_id, $key, true );
							}
						} else {
							carbon_set_user_meta( $new_user_id, $key, $value );
						}
					}
				}

				/**
				 * Allow developers to extend the signup process before firing
				 * the registration confirmation email.
				 *
				 * @param string $new_user_id the user id.
				 * @param Form $form the form.
				 */
				do_action( 'pno_before_registration_end', $new_user_id, $this->form );

				// Send registration confirmation emails.
				pno_send_email(
					'core_user_registration',
					$email_address,
					[
						'user_id'             => $new_user_id,
						'plain_text_password' => $password,
					]
				);

				/**
				 * Allow developers to extend the signup process after firing
				 * the registration confirmation email and before showing the
				 * success message/page.
				 *
				 * @param string $new_user_id the user id.
				 * @param Form $form the form.
				 */
				do_action( 'pno_after_registration', $new_user_id, $this->form );

				// Automatically log a user in if enabled.
				if ( pno_get_option( 'login_after_registration' ) ) {
					pno_log_user_in( $new_user_id );
				}

				if ( pno_get_registration_redirect() ) {
					wp_safe_redirect( pno_get_registration_redirect() );
					exit;
				} else {

					/**
					 * Allow developers to customize the message displayed after successfull registration.
					 *
					 * @param string $message the message that appears after registration.
					 */
					$success_message = apply_filters( 'pno_registration_success_message', esc_html__( 'Registration complete. We have sent you a confirmation email with your details.', 'posterno' ) );

					$this->form->setSuccessMessage( $success_message );
					$this->form->reset();
					return;
				}
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

	/**
	 * List of fields to skip during saving process of custom fields loop.
	 *
	 * @return array
	 */
	private function fieldsToSkip() {

		$fields = [
			'email',
			'password',
			'username',
			'hp-comments',
			'terms',
			'role',
			'privacy',
			'password_confirm',
		];

		return $fields;

	}

}
