<?php
/**
 * Handles display and processing of the login form.
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
 * The class of the login form.
 */
class ListingSubmission {

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
	public $form_name = 'listingSubmission';

	/**
	 * List of steps for the submission.
	 *
	 * @var array
	 */
	public $steps = [];

	/**
	 * Currently active step for the submission.
	 *
	 * @var string
	 */
	public $currentStep = null;

	/**
	 * ID number of the selected listing type, if any.
	 *
	 * @var integer|boolean
	 */
	public $listingType = false;

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var \PNO\Forms\ListingSubmission The single instance of the class
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
		$this->steps       = $this->getSteps();
		$this->currentStep = $this->getCurrentlyActiveStep();
		$this->setListingType();
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
		add_action( 'pno_listing_submission_form_step_listing-type', [ $this, 'selectListingType' ] );
	}

	/**
	 * Retrieve the list of steps defined for the submission form.
	 *
	 * @return array
	 */
	public function getSteps() {

		$steps = [
			'listing-type'    => [
				'name'     => esc_html__( 'Select a listing type', 'posterno' ),
				'priority' => 1,
			],
			'listing-details' => [
				'name'     => esc_html__( 'Listing details', 'posterno' ),
				'priority' => 100,
			],
		];

		/**
		 * Filter: allows customization of the steps for the listing submission form.
		 *
		 * @param array $steps the list of steps.
		 * @return array
		 */
		$steps = apply_filters( 'pno_listing_submission_form_steps', $steps );

		uasort( $steps, 'pno_sort_array_by_priority' );

		return $steps;

	}

	/**
	 * Retrieve the currently active step.
	 *
	 * @return string
	 */
	public function getCurrentlyActiveStep() {

		$step  = false;
		$steps = $this->getSteps();

		if ( is_page( pno_get_listing_submission_page_id() ) && isset( $_GET['submission_step'] ) && ! empty( $_GET['submission_step'] ) ) {
			$step = sanitize_text_field( $_GET['submission_step'] );
		} else {
			$step = key( $steps );
		}

		return $step;

	}

	/**
	 * Get the next available step.
	 *
	 * @return string|boolean
	 */
	public function getNextStep() {
		return pno_get_adjacent_array_key( $this->getCurrentlyActiveStep(), $this->getSteps(), +1 );
	}

	/**
	 * Set a listing type id for the current submisison.
	 *
	 * @return void
	 */
	public function setListingType() {
		$this->listingType = isset( $_GET['listing_type_id'] ) && ! empty( $_GET['listing_type_id'] ) ? absint( $_GET['listing_type_id'] ) : false;
	}

	/**
	 * Get the currently set listing type id number.
	 *
	 * @return string|int|boolean
	 */
	public function getListingType() {
		return absint( $this->listingType );
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
			'submit-form' => [
				'type'       => 'button',
				'value'      => esc_html__( 'Submit listing', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 900,
			],
		];

		$fields = array_merge( pno_get_listing_submission_fields(), $necessaryFields );

		/**
		 * Filter: allows customization of the fields for the listing submission form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_listing_submission_form_fields', $fields );

		uasort( $fields, 'pno_sort_array_by_priority' );

		return $fields;

	}

	/**
	 * Displays the content for the listing type selection step.
	 *
	 * @return void
	 */
	public function selectListingType() {

		posterno()->templates
			->set_template_data(
				[
					'action' => $this->form->getAction(),
					'step'   => $this->getNextStep(),
					'title'  => $this->steps[ $this->getCurrentlyActiveStep() ]['name'],
				]
			)
			->get_template_part( 'forms/listing-type-selection' );

	}

	/**
	 * Render the form.
	 *
	 * @return void
	 */
	public function render() {

		if ( $this->getCurrentlyActiveStep() === 'listing-details' ) {

			$this->form->filterValues();
			$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
						'title'     => esc_html__( 'Listing details', 'posterno' ),
					]
				)
				->get_template_part( 'form' );

		} else {

			$step = $this->getCurrentlyActiveStep();

			/**
			 * Hook: allow developers to display the content of custom listing submission steps.
			 *
			 * @param Form $form the form object.
			 * @param string $next_step the name of the next step
			 */
			do_action( "pno_listing_submission_form_step_{$step}", $this->form, $this->getNextStep() );

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

				/**
				 * Allow developers to extend the listing submission process.
				 * This action is fired before actually creating the new listing.
				 *
				 * @param object $form the class instance managing the form.
				 */
				do_action( 'pno_before_listing_submission', $this->form );

				// Grab main listing details.
				$listing_title       = $this->form->getFieldValue( 'listing_title' );
				$listing_description = ! empty( $this->form->getFieldValue( 'listing_description' ) ) ? $this->form->getFieldValue( 'listing_description' ) : '';
				$listing_author      = get_current_user_id();
				$listing_status      = pno_listing_submission_is_moderated() ? 'pending' : 'publish';

				$listing_data = [
					'post_title'   => $listing_title,
					'post_content' => $listing_description,
					'post_status'  => $listing_status,
					'post_author'  => $listing_author,
					'post_type'    => 'listings',
				];

				$new_listing_id = wp_insert_post( $listing_data );

				if ( is_wp_error( $new_listing_id ) ) {
					throw new Exception( $new_listing_id->get_error_message() );
				} else {

					// Now manipulate the default fields data and store them if necessary.
					if ( ! empty( $this->form->getFieldValue( 'listing_email_address' ) ) ) {
						carbon_set_post_meta( $new_listing_id, 'listing_email', $this->form->getFieldValue( 'listing_email_address' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_phone_number' ) ) ) {
						carbon_set_post_meta( $new_listing_id, 'listing_phone_number', $this->form->getFieldValue( 'listing_phone_number' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_website' ) ) ) {
						carbon_set_post_meta( $new_listing_id, 'listing_website', $this->form->getFieldValue( 'listing_website' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_video' ) ) ) {
						carbon_set_post_meta( $new_listing_id, 'listing_media_embed', $this->form->getFieldValue( 'listing_video' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_zipcode' ) ) ) {
						carbon_set_post_meta( $new_listing_id, 'listing_zipcode', $this->form->getFieldValue( 'listing_zipcode' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_social_media_profiles' ) ) ) {
						pno_save_listing_social_profiles( $new_listing_id, $this->form->getFieldValue( 'listing_social_media_profiles' ) );
					}
					if ( ! empty( $this->form->getFieldValue( 'listing_opening_hours' ) ) ) {
						pno_save_submitted_listing_opening_hours( $new_listing_id, $this->form->getFieldValue( 'listing_opening_hours' ) );
					}
				}

				// Create a featured image for the listing.
				if ( ! empty( $this->form->getFieldValue( 'listing_featured_image' ) ) ) {
					$featured_image = isset( $this->form->getFieldValue( 'listing_featured_image' )['url'] ) ? $this->form->getFieldValue( 'listing_featured_image' )['url'] : $this->form->getFieldValue( 'listing_featured_image' );
					if ( $featured_image ) {
						$attachment_id = $this->form->createAttachment( $new_listing_id, $featured_image );
						if ( $attachment_id ) {
							set_post_thumbnail( $new_listing_id, $attachment_id );
						}
					}
				}

				// Create images for the gallery.
				if ( ! empty( $this->form->getFieldValue( 'listing_gallery' ) ) ) {
					$gallery_images = $this->form->getFieldValue( 'listing_gallery' );
					if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
						$images_list = [];
						foreach ( $gallery_images as $uploaded_file ) {
							$gallery_image_url = isset( $uploaded_file['url'] ) ? $uploaded_file['url'] : $uploaded_file;
							$uploaded_file_id  = $this->form->createAttachment( $new_listing_id, $gallery_image_url );
							if ( $uploaded_file_id ) {
								$images_list[] = [
									'url'  => $gallery_image_url,
									'path' => isset( $uploaded_file['path'] ) ? wp_strip_all_tags( $uploaded_file['path'] ) : pno_content_url_to_local_path( $gallery_image_url ),
								];
							}
						}
						if ( ! empty( $images_list ) ) {
							carbon_set_post_meta( $new_listing_id, 'listing_gallery_images', $images_list );
						}
					}
				}

				// Assign regions.
				if ( ! empty( $this->form->getFieldValue( 'listing_regions' ) ) ) {
					$listing_region  = json_decode( stripslashes( $this->form->getFieldValue( 'listing_regions' ) ) );
					$ancestors       = get_ancestors( $listing_region, 'listings-locations', 'taxonomy' );
					$listing_regions = is_array( $listing_region ) ? $listing_region : [ $listing_region ];
					if ( ! empty( $ancestors ) && is_array( $ancestors ) ) {
						$listing_regions = array_merge( $listing_regions, $ancestors );
					}
					wp_set_object_terms( absint( $new_listing_id ), $listing_regions, 'listings-locations', true );
				}

				// Assign categories to the listing.
				if ( ! empty( $this->form->getFieldValue( 'listing_categories' ) ) ) {
					$listing_categories = json_decode( stripslashes( $this->form->getFieldValue( 'listing_categories' ) ) );
					$listing_categories = array_map( 'absint', $listing_categories );
					if ( ! empty( $listing_categories ) ) {
						wp_set_object_terms( absint( $new_listing_id ), array_unique( $listing_categories ), 'listings-categories' );
					}
				}

				// Assign tags to the listing.
				if ( ! empty( $this->form->getFieldValue( 'listing_tags' ) ) ) {
					$submitted_tags = is_array( $this->form->getFieldValue( 'listing_tags' ) ) ? $this->form->getFieldValue( 'listing_tags' ) : json_decode( stripslashes( $this->form->getFieldValue( 'listing_tags' ) ) );
					$listing_tags   = pno_clean( $submitted_tags );
					$tags           = [];
					if ( is_array( $listing_tags ) ) {
						foreach ( $listing_tags as $tag ) {
							$tags[] = absint( $tag );
						}
					}
					if ( ! empty( $tags ) ) {
						wp_set_object_terms( absint( $new_listing_id ), $tags, 'listings-tags', true );
					}
				}

				// Store location details.
				if ( ! empty( $this->form->getFieldValue( 'listing_location' ) ) ) {
					$location_details = json_decode( stripslashes( $this->form->getFieldValue( 'listing_location' ) ) );
					if ( isset( $location_details->coordinates->lat ) ) {
						pno_update_listing_address( $location_details->coordinates->lat, $location_details->coordinates->lng, $location_details->address, $new_listing_id );
					}
				}

				// Assign the selected listing type to the listing.
				if ( $this->getListingType() ) {
					wp_set_object_terms( absint( $new_listing_id ), $this->getListingType(), 'listings-types', true );
				}

				// Now update the custom fields that are not marked as default.
				foreach ( $this->form->toArray() as $key => $value ) {
					if ( ! pno_is_default_field( $key ) ) {

						$field = $this->form->getField( $key );

						if ( $field->getType() === 'file' ) {

							$is_multiple = $field->isMultiple();

							$attachments_uploaded = $value;

							if ( $is_multiple ) {

								if ( is_array( $attachments_uploaded ) && ! empty( $attachments_uploaded ) ) {
									$new_attachments_list = [];
									foreach ( $attachments_uploaded as $uploaded_file ) {
										$new_attachment_url = isset( $uploaded_file['url'] ) ? $uploaded_file['url'] : $uploaded_file;
										if ( $new_attachment_url ) {
											$uploaded_file_id = $this->form->createAttachment( $new_listing_id, $new_attachment_url );
											if ( $uploaded_file_id ) {
												$new_attachments_list[] = [
													'url'  => $new_attachment_url,
													'path' => isset( $uploaded_file['path'] ) ? wp_strip_all_tags( $uploaded_file['path'] ) : pno_content_url_to_local_path( $new_attachment_url ),
												];
											}
										}
									}
									if ( ! empty( $new_attachments_list ) ) {
										carbon_set_post_meta( $new_listing_id, $key, $new_attachments_list );
									}
								}
							} else {

								if ( isset( $attachments_uploaded['url'] ) ) {
									$attachment_id = $this->form->createAttachment( $new_listing_id, $attachments_uploaded['url'] );
									if ( $attachment_id ) {
										carbon_set_post_meta( $new_listing_id, $key, $attachment_id );
									}
								}
							}
						} elseif ( in_array( $field->getType(), [ 'term-select', 'term-multiselect', 'term-checklist', 'term-chain-dropdown' ] ) ) {

							$term_value = $value;

							if ( $field->getType() === 'term-chain-dropdown' ) {
								$term_value = json_decode( stripslashes( $term_value ) );
							}

							if ( ! empty( $term_value ) ) {
								$this->form->processTaxonomyField( $field, $new_listing_id, $term_value );
							}
						} elseif ( $field->getType() === 'checkbox' ) {
							if ( $value === true || $value === '1' ) {
								carbon_set_post_meta( $new_listing_id, $key, true );
							}
						} else {
							if ( ! empty( $value ) ) {
								carbon_set_post_meta( $new_listing_id, $key, $value );
							}
						}
					}
				}

				/**
				 * Allow developers to extend the listing submission process.
				 * This action is fired after creating the new listing.
				 *
				 * @param object $form the class instance managing the form.
				 * @param string $new_listing_id the id number of the newly created listing.
				 */
				do_action( 'pno_after_listing_submission', $this->form, $new_listing_id );

				$user = get_user_by( 'id', $listing_author );

				// Send email notifications.
				if ( isset( $user->data ) ) {
					pno_send_email(
						'core_user_listing_submitted',
						$user->data->user_email,
						[
							'user_id'    => $listing_author,
							'listing_id' => $new_listing_id,
						]
					);
				}

				// Redirect the user to a new page or display success message.
				$redirect = pno_get_listing_success_redirect_page_id();

				if ( $redirect ) {

					$new_page_url = get_permalink( $redirect );

					/**
					 * Allow developers to adjust the url of the
					 * page displayed after successful submission.
					 *
					 * @param string $new_page_url the url of the page to redirect to.
					 * @param string $new_listing_id the id of the newly created listing.
					 * @param object $form all the data submitted through the form.
					 * @return string
					 */
					$new_page_url = apply_filters( 'pno_successful_listing_submission_redirect_url', $new_page_url, $new_listing_id, $this->form );

					wp_safe_redirect( $new_page_url );
					exit;
				} else {

					$message = sprintf( __( 'Listing successfully submitted. <a href="%s">View your listing.</a>', 'posterno' ), get_permalink( $new_listing_id ) );

					if ( pno_listing_submission_is_moderated() ) {
						$message = esc_html__( 'Listing successfully submitted. Your listing will be visible once approved.', 'posterno' );
					}

					/**
					 * Allow developers to customize the message displayed after successfull listing submission.
					 *
					 * @param string $message the message that appears after listing submission.
					 */
					$success_message = apply_filters( 'pno_listing_submission_success_message', $message );

					$this->form->setSuccessMessage( $success_message );
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
