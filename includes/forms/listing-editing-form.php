<?php
/**
 * Handle the listing editing process.
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
use PNO\Form\Rule\Email;
use PNO\Form\Rule\When;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle the Posterno's listing editing form.
 */
class ListingEditingForm extends Forms {

	/**
	 * Holds the id of the listing we're going to edit.
	 *
	 * @var boolean|int|string
	 */
	public $listing_id = false;

	/**
	 * Holds the id of the user currently trying to edit a listing.
	 *
	 * @var boolean|int|string
	 */
	public $user_id = false;

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form_name    = 'listing_editing_form';
		$this->submit_label = esc_html__( 'Update listing' );
		$this->listing_id   = isset( $_GET['listing_id'] ) ? absint( $_GET['listing_id'] ) : false;
		$this->user_id      = is_user_logged_in() ? get_current_user_id() : false;
		parent::__construct();
	}

	/**
	 * Define the list of fields for the form.
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = [];

		if ( ! is_user_logged_in() || ! is_page( pno_get_listing_editing_page_id() ) || ! $this->listing_id || ! pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) ) {
			return $fields;
		}

		/**
		 * Allows developers to customize fields for the listing editing form.
		 *
		 * @since 0.1.0
		 * @param array $fields array containing the list of fields for the listing editing form.
		 * @param Form  $form the form object.
		 */
		return apply_filters( 'pno_listing_editing_form_fields', $fields, $this->form );

	}

	/**
	 * Form shortcode.
	 *
	 * @return string
	 */
	public function shortcode() {

		ob_start();

		if ( pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) ) {

			posterno()->templates
				->set_template_data(
					[
						'form'         => $this->form,
						'submit_label' => $this->submit_label,
					]
				)
				->get_template_part( 'form' );

		} else {

			posterno()->templates
				->set_template_data(
					[
						'type'    => 'warning',
						'message' => esc_html__( 'You are not authorized to access this page.' ),
					]
				)
				->get_template_part( 'message' );

		}

		return ob_get_clean();

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
	'wp', function () {
		$form = new ListingEditingForm();
		$form->process();
		add_shortcode( 'pno_listing_editing_form', [ $form, 'shortcode' ] );
	}, 30
);
