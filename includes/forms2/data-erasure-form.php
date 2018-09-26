<?php
/**
 * Handle the personal data erasure request form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Forms;

use PNO\Form;
use PNO\Forms;
use PNO\Form\Field\PasswordField;
use PNO\Form\Rule\NotEmpty;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle the Posterno's data erasure form.
 */
class DataErasureForm extends Forms {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form_name    = 'data_erasure_form';
		$this->submit_label = esc_html__( 'Request data cancellation' );
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
					'label'       => esc_html__( 'Current password' ),
					'description' => esc_html__( 'Enter your current password to confim erasure request of your personal data.' ),
					'required'    => true,
					'rules'       => [
						new NotEmpty(),
					],
				]
			),
		);

		/**
		 * Allow developers to customize the data erasure form fields.
		 *
		 * @param array $fields the list of fields.
		 * @return array list of fields.
		 */
		return apply_filters( 'pno_data_erasure_form_fields', $fields );

	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
		add_shortcode( 'pno_request_data_erasure_form', [ $this, 'shortcode' ] );
	}

	/**
	 * Form shortcode.
	 *
	 * @return string
	 */
	public function shortcode() {

		ob_start();

		if ( is_user_logged_in() ) {

			$user = wp_get_current_user();

			posterno()->templates
				->set_template_data(
					[
						'form'         => $this->form,
						'submit_label' => $this->submit_label,
						'title'        => esc_html__( 'Request cancellation of your data' ),
						'message'      => sprintf(
							__( 'You can request cancellation of the data that we have about you. You’ll get an email sent to %s with a link to confirm your request.' ),
							'<strong>' . antispambot( $user->data->user_email ) . '</strong>'
						),
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
	 * @throws \Exception When creating a data request fails.
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

				$user = wp_get_current_user();

				$values = $this->form->get_data();

				$submitted_password = $values['current_password'];

				if ( $user instanceof \WP_User && wp_check_password( $submitted_password, $user->data->user_pass, $user->ID ) && is_user_logged_in() ) {
					$request_id = wp_create_user_request( $user->data->user_email, 'remove_personal_data' );
					if ( is_wp_error( $request_id ) ) {
						throw new \Exception( $request_id->get_error_message() );
					} else {
						wp_send_user_request( $request_id );

						$message = sprintf( esc_html__( 'A confirmation email has been sent to %s. Click the link within the email to confirm your export request.' ), '<strong>' . $user->data->user_email . '</strong>' );

						/**
						 * Allow developers to customize the data erasure success message.
						 *
						 * @param string $message the message.
						 * @return string the new message.
						 */
						$message = apply_filters( 'pno_data_erasure_success_message', $message );

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
		( new DataErasureForm() )->hook();
	}, 30
);
