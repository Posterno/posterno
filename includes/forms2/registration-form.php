<?php
/**
 * Handle the registration form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Forms;

use PNO\Form;
use PNO\Forms;
use PNO\Form\Field\CheckboxField;
use PNO\Form\Field\DropdownField;
use PNO\Form\Field\EditorField;
use PNO\Form\Field\EmailField;
use PNO\Form\Field\MultiCheckboxField;
use PNO\Form\Field\MultiselectField;
use PNO\Form\Field\NumberField;
use PNO\Form\Field\PasswordField;
use PNO\Form\Field\RadioField;
use PNO\Form\Field\TextField;
use PNO\Form\Field\TextAreaField;
use PNO\Form\Field\URLField;

use PNO\Form\Rule\NotEmpty;
use PNO\Form\Rule\PasswordMatches;
use PNO\Form\Rule\StrongPassword;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setup the registration form.
 */
class RegistrationForm extends Forms {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form_name    = 'registration_form';
		$this->submit_label = esc_html__( 'Register' );
		parent::__construct();
	}

	/**
	 * Define the list of fields for the form.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = [];

		$registration_fields = pno_get_registration_fields();

		foreach ( $registration_fields as $field_key => $the_field ) {

			// Get the field type so we can get the class name of the field.
			$field_type       = $the_field['type'];
			$field_type_class = $this->get_field_type_class_name( $field_type );

			// Define validation rules.
			$validation_rules = [];

			if ( isset( $the_field['required'] ) && $the_field['required'] === true ) {
				if ( $field_key === 'terms' ) {
					$validation_rules[] = new NotEmpty( $this->get_tos_validation_error() );
				} elseif ( $field_key === 'privacy' ) {
					$validation_rules[] = new NotEmpty( $this->get_pp_validation_error() );
				} else {
					$validation_rules[] = new NotEmpty();
				}
			}

			// Add validation for password confirmation.
			if ( isset( $registration_fields['password'] ) && isset( $registration_fields['password_confirm'] ) ) {
				if ( $field_key === 'password_confirm' ) {
					$validation_rules[] = new PasswordMatches();
				}
			}

			// Make sure passwords are strong if enabled.
			if ( $field_key === 'password' && pno_get_option( 'strong_passwords' ) ) {
				$validation_rules[] = new StrongPassword();
			}

			// Define additional attributes.
			$attributes = [];
			if ( isset( $the_field['attributes'] ) && ! empty( $the_field['attributes'] ) && is_array( $the_field['attributes'] ) ) {
				$attributes = $the_field['attributes'];
			}

			$fields[] = new $field_type_class(
				$field_key,
				[
					'label'       => $the_field['label'],
					'description' => isset( $the_field['description'] ) ? $the_field['description'] : false,
					'choices'     => isset( $the_field['options'] ) ? $the_field['options'] : false,
					'value'       => isset( $the_field['value'] ) ? $the_field['value'] : false,
					'required'    => (bool) $the_field['required'],
					'rules'       => $validation_rules,
					'attributes'  => $attributes,
				]
			);

		}

		return $fields;

	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
		add_shortcode( 'pno_registration_form', [ $this, 'shortcode' ] );
	}

	/**
	 * Display the registration form.
	 *
	 * @return string
	 */
	public function shortcode() {

		ob_start();

		if ( is_user_logged_in() ) {

			$data = [
				'user' => wp_get_current_user(),
			];

			posterno()->templates
				->set_template_data( $data )
				->get_template_part( 'logged-user' );

		} else {

			posterno()->templates
				->set_template_data(
					[
						'form'         => $this->form,
						'submit_label' => $this->submit_label,
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

		return ob_get_clean();

	}

	/**
	 * Process the registration form.
	 *
	 * @throws \Exception When registration process fails.
	 * @return void
	 */
	public function process() {

		try {
			//phpcs:ignore
			if ( empty( $_POST[ 'submit_' . $this->form->get_name() ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ "{$this->form->get_name()}_nonce" ], "verify_{$this->form->get_name()}_form" ) ) {
				return;
			}

			if ( ! isset( $_POST[ $this->form->get_name() ] ) ) {
				return;
			}

			$this->form->bind( $_POST[ $this->form->get_name() ] );

			if ( $this->form->is_valid() ) {

				$values = $this->form->get_data();

				$email_address = $values['email'];

				// Verify if a username has been submitted.
				// If no username has been supplied, use the email address.
				$has_username = isset( $values['username'] ) && ! empty( $values['username'] ) ? true : false;
				$username     = $email_address;

				if ( $has_username ) {
					$username = $values['username'];
				}

				// Verify if a password has been submitted.
				// If no password has been supplied, generate a random one.
				$has_password = isset( $values['password'] ) && ! empty( $values['password'] ) ? true : false;
				$password     = wp_generate_password( 24, true, true );

				if ( $has_password ) {
					$password = $values['password'];
				}

				/**
				 * Allow developers to extend the signup process before actually
				 * registering the new user.
				 *
				 * @param array $values all the fields submitted through the form.
				 * @param object $this the class instance managing the form.
				 */
				do_action( 'pno_before_registration', $values, $this );

				$new_user_id = wp_create_user( $username, $password, $email_address );

				if ( is_wp_error( $new_user_id ) ) {
					throw new \Exception( $new_user_id->get_error_message() );
				}

				// Assign the role set into the registration form.
				if ( pno_get_option( 'allowed_roles' ) && isset( $values['role'] ) ) {
					$user = new \WP_User( $new_user_id );
					$user->set_role( $values['role'] );
				}

				// Now process all other custom fields.
				foreach ( $values as $key => $value ) {
					if ( $key === 'email' || $key === 'password' || $key === 'username' ) {
						continue;
					}
					if ( pno_is_default_profile_field( $key ) ) {
						update_user_meta( $new_user_id, $key, $value );
					} elseif ( $key == 'website' ) {
						update_user_meta( $new_user_id, 'user_url', $value );
					} else {
						if ( $value == '1' ) {
							carbon_set_user_meta( $new_user_id, $key, true );
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
				 * @param array $values all the fields submitted through the form.
				 * @param object $this the class instance managing the form.
				 */
				do_action( 'pno_before_registration_end', $new_user_id, $values, $this );

				pno_send_registration_confirmation_email( $new_user_id, $password );

				/**
				 * Allow developers to extend the signup process after firing
				 * the registration confirmation email and before showing the
				 * success message/page.
				 *
				 * @param string $new_user_id the user id.
				 * @param array $values all the fields submitted through the form.
				 * @param object $this the class instance managing the form.
				 */
				do_action( 'pno_after_registration', $new_user_id, $values, $this );

				// Automatically log a user in if enabled.
				if ( pno_get_option( 'login_after_registration' ) ) {
					pno_log_user_in( $new_user_id );
				}

				if ( pno_get_registration_redirect() ) {
					wp_safe_redirect( pno_get_registration_redirect() );
					exit;
				} else {
					$success_message = apply_filters( 'wpum_registration_success_message', esc_html__( 'Registration complete. We have sent you a confirmation email with your details.' ) );
					$this->form->unbind();
					$this->form->set_success_message( $success_message );
					return;
				}
			}
		} catch ( \Exception $e ) {
			$this->form->set_processing_error( $e->getMessage() );
			return;
		}

	}

	/**
	 * Retrieve the tos validation error message.
	 *
	 * @return string
	 */
	private function get_tos_validation_error() {
		return apply_filters( 'pno_tos_validation_error_message', esc_html__( 'You must agree to the terms and conditions before registering.' ) );
	}

	/**
	 * Retrieve the privacy policy error message.
	 *
	 * @return string
	 */
	private function get_pp_validation_error() {
		return apply_filters( 'pno_privacy_policy_error_message', esc_html__( 'You must agree to the privacy policy before registering.' ) );
	}

}

add_action(
	'init', function () {
		( new RegistrationForm() )->hook();
	}, 30
);
