<?php
/**
 * Handle the listing submission process.
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
use PNO\Form\Rule\Email;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle the Posterno's listing submission form.
 */
class ListingSubmissionForm extends Forms {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form_name    = 'listing_submission_form';
		$this->submit_label = esc_html__( 'Submit listing' );
		parent::__construct();
	}

	/**
	 * Define the list of fields for the form.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = [];

		$submission_fields = pno_get_listing_submission_fields();

		foreach ( $submission_fields as $field_key => $the_field ) {

			// Get the field type so we can get the class name of the field.
			$field_type       = $the_field['type'];
			$field_type_class = $this->get_field_type_class_name( $field_type );

			// Define validation rules.
			$validation_rules = [];

			if ( isset( $the_field['required'] ) && $the_field['required'] === true ) {
				$validation_rules[] = new NotEmpty();
			}

			if ( $field_type === 'email' ) {
				$validation_rules[] = new Email();
			}

			// Define additional attributes.
			$attributes = [];
			if ( isset( $the_field['attributes'] ) && ! empty( $the_field['attributes'] ) && is_array( $the_field['attributes'] ) ) {
				$attributes = $the_field['attributes'];
			}

			// Attach a placeholder if available.
			if ( isset( $the_field['placeholder'] ) && ! empty( $the_field['placeholder'] ) ) {
				$attributes[ 'placeholder' ] = $the_field['placeholder'];
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
		 * Allows developers to customize fields for the listing submission form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the listing submission form.
		 * @param Form  $form the form object.
		 */
		return apply_filters( 'pno_listing_submission_form_fields', $fields, $this->form );

	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
		add_shortcode( 'pno_listing_submission_form', [ $this, 'shortcode' ] );
	}

	/**
	 * Form shortcode.
	 *
	 * @return string
	 */
	public function shortcode() {

		ob_start();

		$account_required = pno_get_option( 'submission_requires_account' );
		$roles_required   = pno_get_option( 'submission_requires_roles' );

		/**
		 * Allow developers to add custom access restrictions to the submission form.
		 *
		 * @param bool $restricted true or false.
		 * @return bool|string
		 */
		$restricted = apply_filters( 'pno_submission_form_is_restricted', false );

		// Display error message if specific roles are required to access the page.
		if ( is_user_logged_in() && $account_required && $roles_required && is_array( $roles_required ) && ! empty( $roles_required ) ) {

			$user           = wp_get_current_user();
			$role           = (array) $user->roles;
			$roles_selected = [ 'administrator' ];

			foreach ( $roles_required as $single_role ) {
				$roles_selected[] = $single_role['value'];
			}

			if ( ! array_intersect( (array) $user->roles, $roles_selected ) ) {
				$restricted = 'role';
			}
		}

		if ( $restricted ) {

			/**
			 * Allow developers to customize the restriction message for the submission form.
			 *
			 * @param string $message the restriction message.
			 * @param bool|string $restricted wether it's restricted or not and what type of restriction.
			 */
			$message = apply_filters( 'pno_submission_restriction_message', esc_html__( 'Access to this page is restricted.' ), $restricted );

			posterno()->templates
				->set_template_data(
					[
						'type'    => 'warning',
						'message' => $message,
					]
				)
				->get_template_part( 'message' );

		} else {

			posterno()->templates
				->set_template_data(
					[
						'form' => $this->form,
						'step' => $this->get_current_step(),
					]
				)
				->get_template_part( 'listing-submission' );

		}

		return ob_get_clean();

	}

	/**
	 * Determine the current step of the listing submission form.
	 *
	 * @return string
	 */
	public function get_current_step() {

		$step = 'listing_type';

		$type_id = $this->get_submitted_listing_type_id();

		if ( $type_id ) {
			if ( wp_verify_nonce( $_POST[ "listing_type_selection_{$type_id}_nonce" ], "verify_listing_type_selection_{$type_id}_form" ) ) {
				$step = 'submit_listing';
			}
		}

		return $step;

	}

	/**
	 * Detect if a listing type has been selected and retrieve it's id.
	 *
	 * @return mixed
	 */
	public function get_submitted_listing_type_id() {

		$id = false;

		//phpcs:ignore
		if ( isset( $_POST['pno_selected_listing_type_id'] ) && ! empty( $_POST['pno_selected_listing_type_id'] ) ) {
			$id = absint( $_POST['pno_selected_listing_type_id'] );
		}

		return $id;

	}

	/**
	 * Process the form.
	 *
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

			}
		} catch ( \Exception $e ) {
			$this->form->set_processing_error( $e->getMessage() );
			return;
		}

	}

}

add_action(
	'init', function () {
		( new ListingSubmissionForm() )->hook();
	}, 30
);
