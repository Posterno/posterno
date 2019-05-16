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
use PNO\Form\DefaultSanitizer;
use PNO\Validator;
use PNO\Exception;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class of the listing edit form.
 */
class ListingEdit {

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
		$this->addSanitizer( $this->form );
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
				'priority'   => 900,
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

					// Create a featured image for the listing.
					if ( isset( $values['listing_featured_image'] ) ) {

						$attachment_url          = isset( $values['listing_featured_image']['url'] ) ? $values['listing_featured_image']['url'] : $values['listing_featured_image'];
						$currently_uploaded_file = isset( $_POST['current_listing_featured_image'] ) && ! empty( $_POST['current_listing_featured_image'] ) ? sanitize_text_field( $_POST['current_listing_featured_image'] ) : false;

						if ( $attachment_url && ! is_numeric( $attachment_url ) ) {
							// Because we're uploading a new picture, we're removing the old one.
							$thumbnail_id = get_post_thumbnail_id( $updated_listing_id );
							if ( $thumbnail_id ) {
								wp_delete_attachment( $thumbnail_id, true );
								delete_post_thumbnail( $updated_listing_id );
							}
							$attachment_id = $this->form->createAttachment( $updated_listing_id, $attachment_url );
							if ( $attachment_id ) {
								set_post_thumbnail( $updated_listing_id, $attachment_id );
							}
						}

						if ( ! empty( $currently_uploaded_file ) && ! is_numeric( $currently_uploaded_file ) && ! $attachment_url ) {
							$attachment_id = $this->form->createAttachment( $updated_listing_id, $currently_uploaded_file );
							if ( $attachment_id ) {
								set_post_thumbnail( $updated_listing_id, $attachment_id );
							}
						}

						if ( ! $currently_uploaded_file && ! $attachment_url ) {
							$thumbnail_id = get_post_thumbnail_id( $updated_listing_id );
							if ( $thumbnail_id ) {
								wp_delete_attachment( $thumbnail_id, true );
								delete_post_thumbnail( $updated_listing_id );
							}
						}
					}

					// Create images for the gallery.
					if ( isset( $values['listing_gallery'] ) ) {

						// Verify images to remove.
						if ( isset( $_POST['current_listing_gallery'] ) && ! empty( $_POST['current_listing_gallery'] ) && is_array( $_POST['current_listing_gallery'] ) ) {
							$submitted_attachments_ids = $_POST['current_listing_gallery'];
							$current_attachments_ids   = carbon_get_post_meta( $updated_listing_id, 'listing_gallery_images' );
							$submitted_attachments_ids = is_array( $submitted_attachments_ids ) ? array_map( 'absint', $submitted_attachments_ids ) : [];
							$current_attachments_ids   = is_array( $current_attachments_ids ) ? array_map( 'absint', $current_attachments_ids ) : [];
							$removed_attachments_ids   = array_diff( $current_attachments_ids, $submitted_attachments_ids );
							if ( ! empty( $removed_attachments_ids ) && is_array( $removed_attachments_ids ) ) {
								$removed_attachments_ids = array_map( 'absint', $removed_attachments_ids );
								foreach ( $removed_attachments_ids as $removed_att_id ) {
									wp_delete_attachment( $removed_att_id, true );
								}
								$updated_attachments_ids = array_diff( $current_attachments_ids, $removed_attachments_ids );
								carbon_set_post_meta( $updated_listing_id, 'listing_gallery_images', $updated_attachments_ids );
							}
						}

						$gallery_images = $values['listing_gallery'];

						if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
							$images_list = [];
							foreach ( $gallery_images as $uploaded_file ) {
								$attachment_url = isset( $uploaded_file['url'] ) ? $uploaded_file['url'] : $uploaded_file;
								if ( $attachment_url && ! is_numeric( $attachment_url ) ) {
									$uploaded_file_id = $this->form->createAttachment( $updated_listing_id, $attachment_url );
									if ( $uploaded_file_id ) {
										$images_list[] = $uploaded_file_id;
									}
								}
							}
							if ( ! empty( $images_list ) ) {
								$current_attachments = carbon_get_post_meta( $updated_listing_id, 'listing_gallery_images' );
								$new_attachments     = array_merge( $current_attachments, $images_list );
								carbon_set_post_meta( $updated_listing_id, 'listing_gallery_images', $new_attachments );
							}
						}
					}

					// Assign regions.
					if ( ! empty( $this->form->getFieldValue( 'listing_regions' ) ) ) {
						$listing_region  = json_decode( stripslashes( $this->form->getFieldValue( 'listing_regions' ) ) );
						$ancestors       = get_ancestors( $listing_region, 'listings-locations', 'taxonomy' );
						$listing_regions = [ $listing_region ];
						if ( ! empty( $ancestors ) && is_array( $ancestors ) ) {
							$listing_regions = array_merge( $listing_regions, $ancestors );
						}
						wp_set_object_terms( absint( $updated_listing_id ), $listing_regions, 'listings-locations', true );
					}

					// Assign categories to the listing.
					if ( ! empty( $this->form->getFieldValue( 'listing_categories' ) ) ) {
						$listing_categories = json_decode( stripslashes( $this->form->getFieldValue( 'listing_categories' ) ) );
						$listing_categories = array_map( 'absint', $listing_categories );
						if ( ! empty( $listing_categories ) ) {
							wp_set_object_terms( absint( $updated_listing_id ), array_unique( $listing_categories ), 'listings-categories' );
						}
					}

					// Assign tags to the listing.
					if ( ! empty( $this->form->getFieldValue( 'listing_tags' ) ) ) {
						$listing_tags = json_decode( stripslashes( $this->form->getFieldValue( 'listing_tags' ) ) );
						$tags         = [];
						if ( is_array( $listing_tags ) ) {
							foreach ( $listing_tags as $tag ) {
								$tags[] = absint( $tag );
							}
						}
						if ( ! empty( $tags ) ) {
							wp_set_object_terms( absint( $updated_listing_id ), $tags, 'listings-tags', true );
						}
					}

					// Store location details.
					if ( ! empty( $this->form->getFieldValue( 'listing_location' ) ) ) {
						$location_details = json_decode( stripslashes( $this->form->getFieldValue( 'listing_location' ) ) );
						if ( isset( $location_details->coordinates->lat ) ) {
							pno_update_listing_address( $location_details->coordinates->lat, $location_details->coordinates->lng, $location_details->address, $updated_listing_id );
						}
					}

					// Now update the custom fields that are not marked as default.
					foreach ( $this->form->toArray() as $key => $value ) {
						if ( ! pno_is_default_field( $key ) ) {

							$field = $this->form->getField( $key );

							if ( $field->getType() === 'file' ) {

								$is_multiple = $field->isMultiple();

								if ( $is_multiple ) {

									// Verify images to remove.
									if ( isset( $_POST[ "current_{$key}" ] ) && ! empty( $_POST[ "current_{$key}" ] ) && is_array( $_POST[ "current_{$key}" ] ) ) {
										$submitted_attachments_ids = $_POST[ "current_{$key}" ];
										$current_attachments_ids   = carbon_get_post_meta( $updated_listing_id, $key );
										$submitted_attachments_ids = is_array( $submitted_attachments_ids ) ? array_map( 'absint', $submitted_attachments_ids ) : [];
										$current_attachments_ids   = is_array( $current_attachments_ids ) ? array_map( 'absint', $current_attachments_ids ) : [];
										$removed_attachments_ids   = array_diff( $current_attachments_ids, $submitted_attachments_ids );
										if ( ! empty( $removed_attachments_ids ) && is_array( $removed_attachments_ids ) ) {
											$removed_attachments_ids = array_map( 'absint', $removed_attachments_ids );
											foreach ( $removed_attachments_ids as $removed_att_id ) {
												wp_delete_attachment( $removed_att_id, true );
											}
											$updated_attachments_ids = array_diff( $current_attachments_ids, $removed_attachments_ids );
											carbon_set_post_meta( $updated_listing_id, $key, $updated_attachments_ids );
										}
									} elseif ( empty( $values[ $key ] ) ) {

										$current_attachments_ids = carbon_get_post_meta( $updated_listing_id, $key );

										if ( ! empty( $current_attachments_ids ) && is_array( $current_attachments_ids ) ) {
											foreach ( $current_attachments_ids as $att_id ) {
												wp_delete_attachment( $att_id, true );
											}
										}

										carbon_set_post_meta( $updated_listing_id, $key, [] );

									}

									$gallery_images = $values[ $key ];

									if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
										$images_list = [];
										foreach ( $gallery_images as $uploaded_file ) {
											$attachment_url = isset( $uploaded_file['url'] ) ? $uploaded_file['url'] : $uploaded_file;
											if ( $attachment_url && ! is_numeric( $attachment_url ) ) {
												$uploaded_file_id = $this->form->createAttachment( $updated_listing_id, $attachment_url );
												if ( $uploaded_file_id ) {
													$images_list[] = $uploaded_file_id;
												}
											}
										}
										if ( ! empty( $images_list ) ) {
											$current_attachments = carbon_get_post_meta( $updated_listing_id, $key );
											$new_attachments     = array_merge( $current_attachments, $images_list );
											carbon_set_post_meta( $updated_listing_id, $key, $new_attachments );
										}
									}
								} else {

									$attachment_url = isset( $values[ $key ]['url'] ) ? $values[ $key ]['url'] : $values[ $key ];

									if ( $attachment_url && ! is_numeric( $attachment_url ) ) {

										$existing_attachment = carbon_get_post_meta( $updated_listing_id, $key );

										if ( $existing_attachment ) {
											wp_delete_attachment( $existing_attachment, true );
										}

										$attachment_id = $this->form->createAttachment( $updated_listing_id, $values[ $key ]['url'] );
										if ( $attachment_id ) {
											carbon_set_post_meta( $updated_listing_id, $key, $attachment_id );
										}

										if ( isset( $_POST[ "current_{$key}" ] ) && empty( $_POST[ "current_{$key}" ] ) ) {
											wp_delete_attachment( $thumbnail_id, true );
											delete_post_thumbnail( $updated_listing_id );
										}
									} elseif ( empty( $attachment_url ) ) {
										$existing_attachment = carbon_get_post_meta( $updated_listing_id, $key );
										if ( $existing_attachment ) {
											wp_delete_attachment( $existing_attachment, true );
											carbon_set_post_meta( $updated_listing_id, $key, false );
										}
									}
								}
							} elseif ( in_array( $field->getType(), [ 'term-select', 'term-multiselect', 'term-checklist', 'term-chain-dropdown' ] ) ) {

								$term_value = $value;

								if ( $field->getType() === 'term-chain-dropdown' ) {
									$term_value = json_decode( stripslashes( $term_value ) );
								}

								if ( ! empty( $term_value ) ) {
									$this->form->processTaxonomyField( $field, $updated_listing_id, $term_value );
								}
							} elseif ( $field->getType() === 'checkbox' ) {
								if ( $value === true || $value === '1' ) {
									carbon_set_post_meta( $updated_listing_id, $key, true );
								} else {
									carbon_set_post_meta( $updated_listing_id, $key, false );
								}
							} else {
								if ( ! empty( $value ) ) {
									carbon_set_post_meta( $updated_listing_id, $key, $value );
								}
							}
						}
					}

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
