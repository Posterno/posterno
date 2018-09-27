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

				print_r( $values );
				exit;

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
