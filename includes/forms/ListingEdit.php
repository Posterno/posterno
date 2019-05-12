<?php
/**
 * Handles display and processing of the listing editing form.
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
 * The class of the listing edit form.
 */
class ListingEdit {

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
	public $form_name = 'listingEdit';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Login The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * The ID of the user currently editing the listing.
	 *
	 * @var boolean
	 */
	public $user_id = false;

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
		$this->user_id = get_current_user_id();
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
	 * Detect the listing being edited.
	 *
	 * @return string|boolean
	 */
	public function getListingID() {
		return pno_get_queried_listing_editable_id();
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
				'priority'   => 99,
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

		$fields = array_merge( pno_get_listing_submission_fields( $this->getListingID() ), $necessaryFields );

		/**
		 * Filter: allows customization of the fields for the listing editing form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_listing_editing_form_fields', $fields );

		uasort( $fields, 'pno_sort_array_by_priority' );

		return $fields;

	}

	/**
	 * Detect the post status that we're going to apply to the listing based on admin's settings.
	 *
	 * @return string
	 */
	private function is_moderation_required() {
		$status = pno_published_listings_can_be_edited();
		if ( $status === 'yes' ) {
			return 'publish';
		} else {
			return 'pending';
		}
	}

	/**
	 * Render the form.
	 *
	 * @return void
	 */
	public function render() {

		$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
						'title'     => esc_html__( 'Listing details', 'posterno' ),
					]
				)
				->get_template_part( 'new-form' );

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

				/**
				 * Allow developers to extend the listing editing process.
				 * This action is fired before actually editing the listing.
				 *
				 * @param object  $form form instance.
				 * @param string $liting_id the id of the listing being modified.
				 * @param string $user_id the id of the user modifying the listing.
				 */
				do_action( 'pno_before_listing_editing', $this->form, $this->getListingID(), $this->user_id );

				$listing_title       = $this->form->getFieldValue( 'listing_title' );
				$listing_description = ! empty( $this->form->getFieldValue( 'listing_description' ) ) ? $this->form->getFieldValue( 'listing_description' ) : '';

				$listing = array(
					'ID'           => $this->getListingID(),
					'post_title'   => $listing_title,
					'post_content' => $listing_description,
					'post_status'  => $this->is_moderation_required(),
				);

				$updated_listing_id = wp_update_post( $listing );

				if ( is_wp_error( $updated_listing_id ) ) {
					throw new Exception( $updated_listing_id->get_error_message() );
				} else {

					// Now manipulate the default fields data and store them if necessary.
					if ( ! empty( $this->form->getFieldValue( 'listing_email_address' ) ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_email', $this->form->getFieldValue( 'listing_email_address' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_phone_number' ) ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_phone_number', $this->form->getFieldValue( 'listing_phone_number' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_website' ) ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_website', $this->form->getFieldValue( 'listing_website' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_video' ) ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_media_embed', $this->form->getFieldValue( 'listing_video' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_zipcode' ) ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_zipcode', $this->form->getFieldValue( 'listing_zipcode' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_social_media_profiles' ) ) ) {
						pno_save_listing_social_profiles( $updated_listing_id, $this->form->getFieldValue( 'listing_social_media_profiles' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_opening_hours' ) ) ) {
						pno_save_submitted_listing_opening_hours( $updated_listing_id, $this->form->getFieldValue( 'listing_opening_hours' ) );
					}

					$values = $this->form->toArray();

					/**
					 * Allow developers to extend the listing editing process.
					 * This action is fired after all the details of the listing have already been updated.
					 *
					 * @param object $form form instance.
					 * @param string $updated_listing_id the id of the listing being modified.
					 * @param string $user_id the id of the user modifying the listing.
					 */
					do_action( 'pno_after_listing_editing', $this->form, $updated_listing_id, $this->user_id );

					// Now send email notifications to the user.
					$user = get_user_by( 'id', $this->user_id );

					if ( isset( $user->data ) ) {
						pno_send_email(
							'core_user_listing_updated',
							$user->data->user_email,
							[
								'user_id'    => $this->user_id,
								'listing_id' => $updated_listing_id,
							]
						);
					}

					// Now redirect the user.
					$redirect = pno_get_listing_success_edit_redirect_page_id();

					if ( $redirect ) {
						$redirect = get_permalink( $redirect );
					} else {
						$redirect = add_query_arg(
							[
								'message' => 'listing-updated',
							],
							pno_get_dashboard_navigation_item_url( 'listings' )
						);
					}

					/**
					 * Allow developers to adjust the url where members are redirected after
					 * successfully editing one of their listings.
					 *
					 * @param string $redirect the url to redirect to.
					 * @param object $form all the data submitted through the form.
					 * @param string|int $updated_listing_id the id of the listing that was updated.
					 * @param string $user_id user modifying the form.
					 * @return string
					 */
					$redirect = apply_filters( 'pno_listing_successful_editing_redirect_url', $redirect, $this->form, $updated_listing_id, $this->user_id );

					wp_safe_redirect( $redirect );
					exit;
				}
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
