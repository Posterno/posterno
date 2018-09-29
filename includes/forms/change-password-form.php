<?php
/**
 * Handle the password customization form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Forms;

use PNO\Form;
use PNO\Forms;
use PNO\Form\Field\TextField;
use PNO\Form\Field\PasswordField;

use PNO\Form\Rule\NotEmpty;
use PNO\Form\Rule\PasswordMatches;
use PNO\Form\Rule\StrongPassword;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle the Posterno's password customization form.
 */
class ChangePasswordForm extends Forms {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form_name    = 'change_password_form';
		$this->submit_label = esc_html__( 'Change password' );
		parent::__construct();
	}

	/**
	 * Define the list of fields for the form.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = array(
			'current_password' => new PasswordField(
				'current_password',
				[
					'label'    => esc_html__( 'Current password' ),
					'required' => true,
					'rules'    => [
						new NotEmpty(),
					],
				]
			),
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
		);

		// Make sure passwords are strong if enabled.
		if ( pno_get_option( 'strong_passwords' ) ) {
			$fields['password']['rules'][] = new StrongPassword();
		}

		/**
		 * Allows developers to register or deregister fields for the password customization form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the password customization form.
		 * @param Form  $form the form object.
		 */
		return apply_filters( 'pno_change_password_form_fields', $fields, $this->form );

	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
		add_shortcode( 'pno_change_password_form', [ $this, 'shortcode' ] );
	}

	/**
	 * Form shortcode.
	 *
	 * @return string
	 */
	public function shortcode() {

		ob_start();

		if ( is_user_logged_in() ) {

			posterno()->templates
				->set_template_data(
					[
						'form'         => $this->form,
						'submit_label' => $this->submit_label,
						'title'        => esc_html__( 'Change password' ),
					]
				)
				->get_template_part( 'form' );

		}

		return ob_get_clean();

	}

	/**
	 * Process the form.
	 *
	 * @throws \Exception When password verification fails.
	 * @throws \Exception When updating the password fails.
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

				$user = wp_get_current_user();

				$submitted_password = $values['current_password'];

				if ( $user instanceof \WP_User && wp_check_password( $submitted_password, $user->data->user_pass, $user->ID ) && is_user_logged_in() ) {

					$updated_user_id = wp_update_user(
						[
							'ID'        => $user->ID,
							'user_pass' => $values['password'],
						]
					);

					if ( is_wp_error( $updated_user_id ) ) {
						throw new \Exception( $updated_user_id->get_error_message() );
					} else {

						$message = apply_filters( 'pno_password_updated_message', esc_html__( 'Password successfully updated.' ) );

						$this->form->unbind();
						$this->form->set_success_message( $message );

						return;

					}
				} else {
					throw new \Exception( __( 'The password you entered is incorrect.' ) );
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
		( new ChangePasswordForm() )->hook();
	}, 30
);
