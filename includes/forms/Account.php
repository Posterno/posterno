<?php
/**
 * Handles display and processing of the account form.
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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class of the account form.
 */
class Account {

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
	public $form_name = 'account';

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
		add_action( 'wp', [ $this, 'process' ], 20 );
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
				'priority'   => 4,
			],
			'submit'      => [
				'type'       => 'button',
				'value'      => esc_html__( 'Save changes', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 100,
			],
		];

		$fields = array_merge( pno_get_account_fields( get_current_user_id() ), $necessaryFields );

		/**
		 * Filter: allows customization of the fields for the account form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_account_form_fields', $fields );

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
			$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
						'title'     => esc_html__( 'Account settings', 'posterno' ),
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

				$user_id   = get_current_user_id();
				$user_data = [
					'ID' => $user_id,
				];

				/**
				 * Allow developers to customize the form data processed
				 * before the user's account is updated.
				 *
				 * @param object $form the forms class.
				 * @param string $user_id the current user's id.
				 */
				do_action( 'pno_before_user_update', $this->form, $user_id );

				// Update first name and last name.
				if ( $this->form->getFieldValue( 'first_name' ) ) {
					$user_data['first_name'] = $this->form->getFieldValue( 'first_name' );
				}
				if ( $this->form->getFieldValue( 'last_name' ) ) {
					$user_data['last_name'] = $this->form->getFieldValue( 'last_name' );
				}

				// Update email address.
				if ( $this->form->getFieldValue( 'email' ) ) {
					$user_data['user_email'] = $this->form->getFieldValue( 'email' );
				}

				// Update website.
				if ( $this->form->getFieldValue( 'website' ) ) {
					$user_data['user_url'] = $this->form->getFieldValue( 'website' );
				}

				if ( $this->form->getFieldValue( 'description' ) ) {
					$user_data['description'] = $this->form->getFieldValue( 'description' );
				}

				$updated_user_id = wp_update_user( $user_data );

				if ( is_wp_error( $updated_user_id ) ) {
					throw new Exception( $updated_user_id->get_error_message(), $updated_user_id->get_error_code() );
				}

				// Now update the custom fields that are not marked as default profile fields.
				foreach ( $this->form->toArray() as $key => $value ) {
					if ( ! empty( $this->form->getFieldValue( $key ) ) && ! pno_is_default_field( $key ) ) {

						$field = $this->form->getField( $key );

						if ( $field->getType() === 'file' ) {

						} elseif ( $field->getType() === 'checkbox' ) {
							if ( $value === true ) {
								carbon_set_user_meta( $updated_user_id, $key, true );
							} else {
								delete_user_meta( $updated_user_id, '_' . $key );
							}
						} else {
							carbon_set_user_meta( $updated_user_id, $key, $value );
						}
					} elseif ( empty( $this->form->getFieldValue( $key ) ) && ! pno_is_default_field( $key ) ) {
						carbon_set_user_meta( $updated_user_id, $key, false );
						delete_user_meta( $updated_user_id, '_' . $key );
					}
				}

				/**
				 * Action that fires after the user's account has been update,
				 * all fields values have been processed and stored within the user's account.
				 *
				 * @param object $form the form's object.
				 * @param string $user_id the current user's id being processed.
				 */
				do_action( 'pno_after_user_update', $this->form, $updated_user_id );

				/**
				 * Allow developers to customize the message displayed after successfull account update.
				 *
				 * @param string $message the message that appears after account update.
				 */
				$message = apply_filters( 'pno_account_updated_message', esc_html__( 'Account details successfully updated.', 'posterno' ) );

				$this->form->setSuccessMessage( $message );
				return;
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
