<?php
/**
 * Handle the password recovery form.
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
use PNO\Form\Field\DropzoneField;
use PNO\Form\Field\EditorField;
use PNO\Form\Field\EmailField;
use PNO\Form\Field\FileField;
use PNO\Form\Field\ListingCategoryField;
use PNO\Form\Field\ListingLocationField;
use PNO\Form\Field\ListingOpeningHoursField;
use PNO\Form\Field\ListingTagsField;
use PNO\Form\Field\MultiCheckboxField;
use PNO\Form\Field\MultiSelectField;
use PNO\Form\Field\NumberField;
use PNO\Form\Field\PasswordField;
use PNO\Form\Field\RadioField;
use PNO\Form\Field\SocialProfilesField;
use PNO\Form\Field\TermSelectField;
use PNO\Form\Field\TextAreaField;
use PNO\Form\Field\TextField;
use PNO\Form\Field\URLField;
use PNO\Form\Rule\NotEmpty;
use PNO\Form\Rule\PasswordMatches;
use PNO\Form\Rule\StrongPassword;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle the Posterno's password recovery form.
 */
class PasswordRecoveryForm extends Forms {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form_name    = 'password_recovery_form';
		$this->submit_label = esc_html__( 'Reset password' );
		parent::__construct();
	}

	/**
	 * Determine if we've got a key and user id to display the password fields and update the password.
	 *
	 * @return boolean
	 */
	private function is_recovery_mode() {
		//phpcs:ignore
		return isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) && isset( $_GET['key'] ) && ! empty( $_GET['key'] ) ? true : false;
	}

	/**
	 * Define the list of fields for the form.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = array(
			'username_email' => new TextField(
				'username_email',
				[
					'label'    => __( 'Username or email' ),
					'required' => true,
					'rules'    => [
						new NotEmpty(),
					],
				]
			),
		);

		// Modify the fields if we're now resetting the password.
		if ( $this->is_recovery_mode() ) {
			unset( $fields['username_email'] );

			$fields = [
				'password'         => new PasswordField(
					'password',
					[
						'label'    => esc_html__( 'New password' ),
						'required' => true,
						'rules'    => [
							new NotEmpty(),
						],
					]
				),
				'password_confirm' => new PasswordField(
					'password_confirm',
					[
						'label'    => esc_html__( 'Repeat new password' ),
						'required' => true,
						'rules'    => [
							new NotEmpty(),
							new PasswordMatches(),
						],
					]
				),
			];

			// Make sure passwords are strong if enabled.
			if ( pno_get_option( 'strong_passwords' ) ) {
				$fields['password']['rules'][] = new StrongPassword();
			}
		}

		/**
		 * Allows developers to register or deregister fields for the password recovery form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the password recovery form.
		 * @param Form  $form the form object.
		 */
		return apply_filters( 'pno_password_recovery_form_fields', $fields, $this->form );

	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
		add_shortcode( 'pno_password_recovery_form', [ $this, 'shortcode' ] );
	}

	/**
	 * Login form shortcode.
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
						'title'        => $this->is_recovery_mode() ? esc_html__( 'Enter a new password below.' ) : false,
						'message'      => ! $this->is_recovery_mode() ? esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.' ) : false,
					]
				)
				->get_template_part( 'form' );

			$action_links = [
				'login_link'    => pno_get_option( 'recovery_show_login_link' ),
				'register_link' => pno_get_option( 'recovery_show_registration_link' ),
			];

			posterno()->templates
				->set_template_data( $action_links )
				->get_template_part( 'forms/action-links' );

		}

		return ob_get_clean();

	}

	/**
	 * Process the form.
	 *
	 * @throws \Exception When authentication process fails.
	 * @throws \Exception When login process fails.
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

				// Reset the password given a valid key.
				if ( $this->is_recovery_mode() ) {

					$user_id          = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;
					$get_user         = get_user_by( 'id', $user_id );
					$verification_key = isset( $_GET['key'] ) && ! empty( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : false;

					if ( $user_id && $get_user instanceof \WP_User && $verification_key ) {

						$verify_key = check_password_reset_key( $verification_key, $get_user->data->user_login );

						if ( is_wp_error( $verify_key ) ) {

							/**
							 * Allow developers to customize the error message that appears
							 * when the password reset key is invalid.
							 *
							 * @param string $message the error message.
							 * @return string the new message.
							 */
							$error_message = apply_filters( 'pno_password_recovery_key_invalid_message', esc_html__( 'The reset key is wrong or expired. Please check that you used the right reset link or request a new one.' ) );

							throw new \Exception( $error_message );

						} else {

							$password_1 = $values['password'];
							$password_2 = $values['password_confirm'];
							$user_id    = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;

							wp_set_password( $password_1, $user_id );

							// Clear all user sessions.
							$sessions = \WP_Session_Tokens::get_instance( $user_id );
							$sessions->destroy_all();

							$success_message = esc_html__( 'Password successfully reset.' ) . ' ' . '<a href="' . get_permalink( pno_get_login_page_id() ) . '">' . esc_html__( 'Login now &raquo;' ) . '</a>';

							/**
							 * Allow developers to customize the password recovery success message.
							 *
							 * @param string $message the success message.
							 * @return string the new message.
							 */
							$success_message = apply_filters( 'pno_password_recovery_success_message', $success_message );

							$this->form->unbind();
							$this->form->set_success_message( $success_message );
							return;

						}
					} else {

						/**
						 * Allow developers to customize the password recovery invalid link message.
						 *
						 * @param string $message the error message.
						 * @return string the new message.
						 */
						$invalid_link = apply_filters( 'pno_password_recovery_invalid_link_message', esc_html__( 'The link you followed may be broken. Please check that you used the right reset link or request a new one.' ) );

						throw new \Exception( $invalid_link );

					}
				} else {

					$username = $values['username_email'];
					$user     = false;

					if ( is_email( $username ) && ! email_exists( $username ) || ! is_email( $username ) && ! username_exists( $username ) ) {

						/**
						 * Allow developers to customize the password recovery user not found message.
						 *
						 * @param string $message the error message.
						 * @return string the new message.
						 */
						$user_not_found_message = apply_filters( 'pno_password_recovery_invalid_user', esc_html__( 'A user with this username or email does not exist. Please check your entry and try again.' ) );

						throw new \Exception( $user_not_found_message );
					}

					// Retrieve the user from the DB.
					if ( is_email( $username ) ) {
						$user = \get_user_by( 'email', $username );
					} else {
						$user = \get_user_by( 'login', $username );
					}

					if ( $user instanceof \WP_User ) {

						// Generate a new password reset key for the selected user.
						$password_reset_key = get_password_reset_key( $user );

						// Now send an email to the user.
						if ( $password_reset_key ) {

							$subject = pno_get_option( 'password_recovery_subject' );
							$message = pno_get_option( 'password_recovery_content' );
							$heading = pno_get_option( 'password_recovery_heading' );

							posterno()->emails->__set( 'user_id', $user->data->ID );
							if ( $heading ) {
								posterno()->emails->__set( 'heading', $heading );
							}
							posterno()->emails->__set( 'password_reset_key', $password_reset_key );
							posterno()->emails->send( $user->data->user_email, $subject, $message );

							$masked_email    = pno_mask_email_address( $user->data->user_email );
							$success_message = sprintf( esc_html__( 'We\'ve sent an email to %s with password reset instructions.' ), '<strong>' . $masked_email . '</strong>' );

							/**
							 * Allow developers to customize the success message for the password
							 * recovery form when the recovery email has been sent.
							 *
							 * @param string $success_message the message.
							 * @return string the new message.
							 */
							$success_message = apply_filters( 'pno_password_recovery_success_mail_sent', $success_message );

							$this->form->unbind();
							$this->form->set_success_message( $success_message );
							return;

						}
					} else {

						throw new \Exception( esc_html__( 'Something went wrong.' ) );

					}
				}
			}
		} catch ( \Exception $e ) {
			$this->form->set_processing_error( $e->getMessage() );
			return;
		}

	}

}

add_action(
	'init', function () {
		( new PasswordRecoveryForm() )->hook();
	}, 30
);
