<?php
/**
 * Handles display and processing of the account delete form.
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
 * The class of the account delete form.
 */
class DeleteAccount {

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
	public $form_name = 'deleteAccount';

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

		$fields = [
			'password'    => [
				'type'       => 'password',
				'label'      => esc_html__( 'Current password', 'posterno' ),
				'hint'       => esc_html__( 'Enter your current password to confirm cancellation of your account.', 'posterno' ),
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
				'value'      => esc_html__( 'Delete account', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 100,
			],
		];

		/**
		 * Filter: allows customization of the fields for the account delete form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_delete_account_form_fields', $fields );

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

			/**
			 * Allow developers to customize the message displayed within
			 * the account cancellation form.
			 *
			 * @param string $message
			 * @return string
			 */
			$message = apply_filters( 'pno_delete_account_form_message', esc_html__( 'The account will no longer be available, and all data in the account will be permanently deleted.', 'posterno' ) );

			$this->form->filterValues();
			$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
						'title'     => esc_html__( 'Permanently delete account', 'posterno' ),
						'message'   => $message,
					]
				)
				->get_template_part( 'form' );

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

				wp_logout();

				require_once ABSPATH . 'wp-admin/includes/user.php';

				wp_delete_user( $user->ID );

				$redirect_to = pno_get_option( 'cancellation_redirect' );

				if ( is_array( $redirect_to ) && isset( $redirect_to[0] ) && ! empty( $redirect_to[0] ) ) {
					wp_safe_redirect( get_permalink( $redirect_to[0] ) );
					exit;
				} else {
					wp_safe_redirect( home_url() );
					exit;
				}
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
