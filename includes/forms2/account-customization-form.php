<?php
/**
 * Handle the account customization form.
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
use PNO\Form\Field\FileField;

use PNO\Form\Rule\NotEmpty;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle the Posterno's account customization form.
 */
class AccountCustomizationForm extends Forms {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form_name    = 'account_customization_form';
		$this->submit_label = esc_html__( 'Update account' );
		$this->object_type  = 'user_meta';
		parent::__construct();
	}

	/**
	 * Define the list of fields for the form.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = [];

		$account_fields = pno_get_account_fields( get_current_user_id() );

		foreach ( $account_fields as $field_key => $the_field ) {

			// Get the field type so we can get the class name of the field.
			$field_type       = $the_field['type'];
			$field_type_class = $this->get_field_type_class_name( $field_type );

			// Define validation rules.
			$validation_rules = [];

			if ( isset( $the_field['required'] ) && $the_field['required'] === true ) {
				$validation_rules[] = new NotEmpty();
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

		/**
		 * Allow developers to customize the fields within the account customization form.
		 * Fields here have already been formatted as objects for the form.
		 *
		 * @param array $fields the list of fields formatted for the form.
		 * @param Form $form the form object.
		 * @return array the list of fields formatted for the form.
		 */
		return apply_filters( 'pno_account_customization_form_fields', $fields, $this->form );

	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
		add_shortcode( 'pno_account_customization_form', [ $this, 'shortcode' ] );
	}

	/**
	 * Account customization form shortcode.
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
						'title'        => esc_html__( 'Account settings' ),
					]
				)
				->get_template_part( 'form' );

		}

		return ob_get_clean();

	}

	/**
	 * Process the form.
	 *
	 * @throws \Exception When submission process fails.
	 * @throws \Exception When updating the user fails.
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

				$user_id = get_current_user_id();

				$user_data = [
					'ID' => $user_id,
				];

				/**
				 * Allow developers to customize the form data processed
				 * before the user's account is updated.
				 *
				 * @param object $form the forms class.
				 * @param array $values the form data collected.
				 * @param string $user_id the current user's id.
				 */
				do_action( 'pno_before_user_update', $this, $values, $user_id );

				// Update first name and last name.
				if ( isset( $values['first_name'] ) ) {
					$user_data['first_name'] = $values['first_name'];
				}
				if ( isset( $values['last_name'] ) ) {
					$user_data['last_name'] = $values['last_name'];
				}

				// Update email address.
				if ( isset( $values['email'] ) ) {
					$user_data['user_email'] = $values['email'];
				}

				// Update website.
				if ( isset( $values['website'] ) ) {
					$user_data['user_url'] = $values['website'];
				}

				if ( isset( $values['description'] ) ) {
					$user_data['description'] = $values['description'];
				}

				$updated_user_id = wp_update_user( $user_data );

				if ( is_wp_error( $updated_user_id ) ) {
					throw new \Exception( $updated_user_id->get_error_message() );
				}

				// Update the avatar.
				if ( pno_get_option( 'allow_avatars' ) ) {
					$currently_uploaded_file   = isset( $_POST['current_avatar'] ) && ! empty( $_POST['current_avatar'] ) ? esc_url_raw( $_POST['current_avatar'] ) : false;
					$existing_avatar_file_path = get_user_meta( $updated_user_id, 'current_user_avatar_path', true );
					if ( $currently_uploaded_file && $existing_avatar_file_path && isset( $values['avatar']['url'] ) && $values['avatar']['url'] !== $currently_uploaded_file ) {
						wp_delete_file( $existing_avatar_file_path );
					}
					if ( isset( $values['avatar']['url'] ) && $currently_uploaded_file !== $values['avatar']['url'] ) {
						carbon_set_user_meta( $updated_user_id, 'current_user_avatar', $values['avatar']['url'] );
						update_user_meta( $updated_user_id, 'current_user_avatar_path', $values['avatar']['path'] );
					}
					if ( ! $currently_uploaded_file && file_exists( $existing_avatar_file_path ) ) {
						wp_delete_file( $existing_avatar_file_path );
						carbon_set_user_meta( $updated_user_id, 'current_user_avatar', false );
						delete_user_meta( $updated_user_id, 'current_user_avatar_path' );
					}
				}

				// Now update the custom fields that are not marked as default profile fields.
				foreach ( $values as $key => $value ) {
					if ( ! pno_is_default_profile_field( $key ) ) {
						if ( $value == '1' ) {
							carbon_set_user_meta( $updated_user_id, $key, true );
						} elseif ( is_array( $value ) && isset( $value['url'] ) && isset( $value['path'] ) ) {

							$currently_uploaded_file = isset( $_POST[ "current_{$key}" ] ) && ! empty( $_POST[ "current_{$key}" ] ) ? esc_url_raw( $_POST[ "current_{$key}" ] ) : false;
							$existing_file_path      = get_user_meta( $updated_user_id, "current_{$key}", true );

							if ( $currently_uploaded_file && $existing_file_path && isset( $values[ $key ]['url'] ) && $values[ $key ]['url'] !== $currently_uploaded_file ) {
								wp_delete_file( $existing_file_path );
							}

							carbon_set_user_meta( $updated_user_id, $key, $value['url'] );
							update_user_meta( $updated_user_id, "current_{$key}", $value['path'] );

						} else {
							carbon_set_user_meta( $updated_user_id, $key, $value );
						}
					}
				}

				/**
				 * Action that fires after the user's account has been update,
				 * all fields values have been processed and stored within the user's account.
				 *
				 * @param object $form the form's object.
				 * @param array $values the array of data submitted through the form.
				 * @param string $user_id the current user's id being processed.
				 */
				do_action( 'pno_after_user_update', $this, $values, $updated_user_id );

				/**
				 * Allow developers to customize the message displayed after successfull account update.
				 *
				 * @param string $message the message that appears after account update.
				 */
				$message = apply_filters( 'pno_account_updated_message', esc_html__( 'Account details successfully updated.' ) );

				$this->form->set_success_message( $message );
				return;

			}
		} catch ( \Exception $e ) {
			$this->form->set_processing_error( $e->getMessage() );
			return;
		}

	}

}

add_action(
	'init', function () {
		( new AccountCustomizationForm() )->hook();
	}, 30
);
