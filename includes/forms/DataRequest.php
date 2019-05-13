<?php
/**
 * Handles display and processing of the data request form.
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
 * The class of the data request form.
 */
class DataRequest {

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
	public $form_name = 'dataRequest';

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
		add_shortcode( 'pno_request_data_form', [ $this, 'render' ] );
		add_action( 'wp_loaded', [ $this, 'process' ] );
	}

	/**
	 * Get fields for the form.
	 *
	 * @return array
	 */
	protected function getFields() {

		$fields = [
			'password'    => [
				'type'       => 'password',
				'label'      => esc_html__( 'Current password', 'posterno' ),
				'hint'       => esc_html__( 'Enter your current password to confim export of your personal data.', 'posterno' ),
				'required'   => true,
				'validators' => new Validator\VerifyPassword(),
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 1,
			],
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
				'priority'   => 4,
			],
			'submit'      => [
				'type'       => 'button',
				'value'      => esc_html__( 'Request data', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 100,
			],
		];

		/**
		 * Filter: allows customization of the fields for the account data request form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_data_request_form_fields', $fields );

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

			$user = wp_get_current_user();

			$message = apply_filters(
				'pno_data_request_form_message',
				sprintf(
					__( 'You can request a file with the information that we believe is most relevant and useful to you. You’ll get an email sent to %s with a link when it’s ready to be downloaded.', 'posterno' ),
					'<strong>' . antispambot( $user->data->user_email ) . '</strong>'
				)
			);

			$this->form->filterValues();
			$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
						'title'     => esc_html__( 'Download your data', 'posterno' ),
						'message'   => $message,
					]
				)
				->get_template_part( 'new-form' );

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

				$user = wp_get_current_user();

				$request_id = wp_create_user_request( $user->user_email, 'export_personal_data' );

				if ( is_wp_error( $request_id ) ) {
					throw new Exception( $request_id->get_error_message(), $request_id->get_error_code() );
				} else {
					wp_send_user_request( $request_id );

					$message = sprintf( esc_html__( 'A confirmation email has been sent to %s. Click the link within the email to confirm your export request.', 'posterno' ), '<strong>' . $user->data->user_email . '</strong>' );

					/**
					 * Allow developers to customize the data request form success message.
					 *
					 * @param string $message the success message.
					 * @return string the new message.
					 */
					$message = apply_filters( 'pno_personal_data_request_success_message', $message );

					$this->form->setSuccessMessage( $message );
					$this->form->reset();
					return;
				}
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
