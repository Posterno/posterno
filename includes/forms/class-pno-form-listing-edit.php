<?php
/**
 * Handles display and processing of the listing editing form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles the form.
 */
class PNO_Form_Listing_Edit extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'listing-edit';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Listing_Editi The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Holds the ID of the listing we're going to modify.
	 *
	 * @var integer
	 */
	public $listing_id = 0;

	/**
	 * Holds the ID of the user that wants to edit the listing.
	 *
	 * @var integer
	 */
	public $user_id = 0;

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
	 * Constructor.
	 */
	public function __construct() {

		$this->listing_id = pno_get_queried_listing_editable_id();

		$this->user_id = $this->get_user_id();

		if (
			! is_user_logged_in() ||
			! is_page( pno_get_listing_editing_page_id() ) ||
			! $this->listing_id ||
			! pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) ||
			pno_is_listing_expired( $this->listing_id ) ||
			( pno_is_listing_pending_approval( $this->listing_id ) && pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) && ! pno_pending_listings_can_be_edited() ) ) {
			return;
		}

		add_action( 'wp', array( $this, 'process' ) );

		$steps = array(
			'listing-details' => array(
				'name'     => esc_html__( 'Listing details', 'posterno' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 20,
			),
		);

		/**
		 * List of steps for the listing editing form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the listing editing form.
		 */
		$this->steps = (array) apply_filters( 'pno_listing_editing_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( intval( $_POST['step'] ), array_keys( $this->steps ), true );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( intval( $_GET['step'] ), array_keys( $this->steps ), true );
		}

	}

	/**
	 * Retrieve the ID of the listing we're going to modify.
	 *
	 * @return mixed
	 */
	private function get_listing_id() {
		return isset( $_GET['listing_id'] ) ? absint( $_GET['listing_id'] ) : false;
	}

	/**
	 * Retrieve the ID of the user currently logged in.
	 *
	 * @return mixed
	 */
	private function get_user_id() {
		return is_user_logged_in() ? get_current_user_id() : false;
	}

	/**
	 * Initializes the fields used in the form.
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$fields = array(
			'listing-details' => pno_get_listing_submission_fields( $this->listing_id ),
		);

		$this->fields = $fields;

	}

	/**
	 * Displays the listing details form.
	 *
	 * @return void
	 */
	public function submit() {

		$this->init_fields();

		$data = [
			'form'         => $this,
			'action'       => $this->get_action(),
			'fields'       => $this->get_fields( 'listing-details' ),
			'step'         => $this->get_step(),
			'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
			'submit_label' => esc_html__( 'Save changes', 'posterno' ),
			'form_type'    => 'listing',

		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'form' );

	}

	/**
	 * Handles the submission of form data.
	 *
	 * @throws Exception On validation error.
	 */
	public function submit_handler() {
		try {

			if ( empty( $_POST[ 'submit_' . $this->form_name ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ "{$this->form_name}_nonce" ], "verify_{$this->form_name}_form" ) ) {
				return;
			}

			//phpcs:ignore
			if ( ! isset( $_POST[ 'pno_form' ] ) || isset( $_POST['pno_form'] ) && $_POST['pno_form'] !== $this->form_name ) {
				return;
			}

			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			$values = $values['listing-details'];

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			/**
			 * Allow developers to extend the listing editing process.
			 * This action is fired before actually editing the listing.
			 *
			 * @param array  $values all the fields submitted through the form.
			 * @param string $liting_id the id of the listing being modified.
			 * @param string $user_id the id of the user modifying the listing.
			 */
			do_action( 'pno_before_listing_editing', $values, $this->listing_id, $this->user_id );

			$listing = array(
				'ID'           => $this->listing_id,
				'post_title'   => $values['listing_title'],
				'post_content' => isset( $values['listing_description'] ) ? $values['listing_description'] : '',
				'post_status'  => $this->is_moderation_required(),
			);

			$updated_listing_id = wp_update_post( $listing );

			if ( is_wp_error( $updated_listing_id ) ) {
				throw new Exception( $updated_listing_id->get_error_message() );
			} else {

				if ( isset( $values['listing_email_address'] ) && ! empty( $values['listing_email_address'] ) ) {
					carbon_set_post_meta( $updated_listing_id, 'listing_email', $values['listing_email_address'] );
				}
				if ( isset( $values['listing_phone_number'] ) && ! empty( $values['listing_phone_number'] ) ) {
					carbon_set_post_meta( $updated_listing_id, 'listing_phone_number', $values['listing_phone_number'] );
				}
				if ( isset( $values['listing_website'] ) && ! empty( $values['listing_website'] ) ) {
					carbon_set_post_meta( $updated_listing_id, 'listing_website', $values['listing_website'] );
				}
				if ( isset( $values['listing_video'] ) && ! empty( $values['listing_video'] ) ) {
					carbon_set_post_meta( $updated_listing_id, 'listing_media_embed', $values['listing_video'] );
				}
				if ( isset( $values['listing_zipcode'] ) && ! empty( $values['listing_zipcode'] ) ) {
					carbon_set_post_meta( $updated_listing_id, 'listing_zipcode', $values['listing_zipcode'] );
				}
				if ( isset( $values['listing_social_media_profiles'] ) && ! empty( $values['listing_social_media_profiles'] ) ) {
					pno_save_listing_social_profiles( $updated_listing_id, $values['listing_social_media_profiles'] );
				}
				if ( isset( $values['listing_opening_hours'] ) && ! empty( $values['listing_opening_hours'] ) ) {
					pno_save_submitted_listing_opening_hours( $updated_listing_id, $values['listing_opening_hours'] );
				}

				// Create a featured image for the listing.
				if ( isset( $values['listing_featured_image'] ) ) {

					$attachment_url = isset( $values['listing_featured_image']['url'] ) ? $values['listing_featured_image']['url'] : $values['listing_featured_image'];

					if ( $attachment_url && ! is_numeric( $attachment_url ) ) {

						// Because we're uploading a new picture, we're removing the old one.
						$thumbnail_id = get_post_thumbnail_id( $updated_listing_id );

						if ( $thumbnail_id ) {
							wp_delete_attachment( $thumbnail_id, true );
							delete_post_thumbnail( $updated_listing_id );
						}

						$attachment_id = $this->create_attachment( $updated_listing_id, $attachment_url );
						if ( $attachment_id ) {
							set_post_thumbnail( $updated_listing_id, $attachment_id );
						}

						if ( isset( $_POST['current_listing_featured_image'] ) && empty( $_POST['current_listing_featured_image'] ) ) {
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
								$uploaded_file_id = $this->create_attachment( $updated_listing_id, $attachment_url );
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

				// Assign terms.
				if ( isset( $values['listing_regions'] ) && ! empty( $values['listing_regions'] ) ) {
					$listing_region  = json_decode( $values['listing_regions'] );
					$ancestors       = get_ancestors( $listing_region, 'listings-locations', 'taxonomy' );
					$listing_regions = [ $listing_region ];
					if ( ! empty( $ancestors ) && is_array( $ancestors ) ) {
						$listing_regions = array_merge( $listing_regions, $ancestors );
					}
					wp_set_object_terms( absint( $updated_listing_id ), $listing_regions, 'listings-locations', true );
				}

				// Assign categories to the listing.
				if ( isset( $values['listing_categories'] ) && ! empty( $values['listing_categories'] ) ) {
					$listing_categories = json_decode( $values['listing_categories'] );
					$listing_categories = array_map( 'absint', $listing_categories );
					if ( ! empty( $listing_categories ) ) {
						wp_set_object_terms( absint( $updated_listing_id ), array_unique( $listing_categories ), 'listings-categories' );
					}
				}

				// Assign tags to the listing.
				if ( isset( $values['listing_tags'] ) && ! empty( $values['listing_tags'] ) ) {
					$listing_tags = json_decode( $values['listing_tags'] );
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

				if ( isset( $values['listing_location'] ) && ! empty( $values['listing_location'] ) ) {
					$location_details = json_decode( $values['listing_location'] );
					if ( isset( $location_details->coordinates->lat ) ) {
						pno_update_listing_address( $location_details->coordinates->lat, $location_details->coordinates->lng, $location_details->address, $updated_listing_id );
					}
				}

				foreach ( $this->fields['listing-details'] as $key => $field_details ) {

					if ( isset( $values[ $key ] ) && ! pno_is_default_field( $key ) ) {

						if ( $field_details['type'] === 'file' ) {

							$is_multiple = isset( $field_details['multiple'] ) && $field_details['multiple'] === true ? true : false;

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
								} elseif ( empty ( $values[ $key ] ) ) {

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
											$uploaded_file_id = $this->create_attachment( $updated_listing_id, $attachment_url );
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

									$attachment_id = $this->create_attachment( $updated_listing_id, $values[ $key ]['url'] );
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
						} elseif ( in_array( $field_details['type'], [ 'term-select', 'term-multiselect', 'term-checklist', 'term-chain-dropdown' ] ) ) {

							if ( ! empty( $values[ $key ] ) ) {
								$this->process_taxonomy_field( $field_details, $updated_listing_id, $values[ $key ] );
							}
						} elseif ( $field_details['type'] === 'checkbox' ) {

							if ( $values[ $key ] === '1' ) {
								carbon_set_post_meta( $updated_listing_id, $key, true );
							} else {
								carbon_set_post_meta( $updated_listing_id, $key, false );
							}
						} else {

							if ( ! empty( $values[ $key ] ) ) {
								carbon_set_post_meta( $updated_listing_id, $key, $values[ $key ] );
							}
						}
					}
				}

				/**
				 * Allow developers to extend the listing editing process.
				 * This action is fired after all the details of the listing have already been updated.
				 *
				 * @param array $values all the fields submitted through the form.
				 * @param string $updated_listing_id the id number of the newly created listing.
				 * @param string $user_id user modifying the form.
				 */
				do_action( 'pno_after_listing_editing', $values, $updated_listing_id, $this->user_id );

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
				 * @param array $values all the data submitted through the form.
				 * @param string|int $updated_listing_id the id of the listing that was updated.
				 * @param string $user_id user modifying the form.
				 * @return string
				 */
				$redirect = apply_filters( 'pno_listing_successful_editing_redirect_url', $redirect, $values, $updated_listing_id, $this->user_id );

				wp_safe_redirect( $redirect );
				exit;

			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
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

}
