<?php
/**
 * Handles display and processing of the listing submission form.
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
class PNO_Form_Listing_Submission extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'listing-submission';

	/**
	 * Holds the selected listing type id.
	 *
	 * @var string
	 */
	public $listing_type_id = null;

	/**
	 * ID number of the newly created listing,
	 *
	 * @var integer
	 */
	public $new_listing_id = 0;

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Listing_Submission The single instance of the class
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
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'process' ) );

		$steps = array(
			'listing-type'      => array(
				'name'     => esc_html__( 'Select a listing type' ),
				'view'     => array( $this, 'type' ),
				'handler'  => array( $this, 'type_handler' ),
				'priority' => 10,
			),
			'listing-details'   => array(
				'name'     => esc_html__( 'Listing details' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 20,
			),
			'listing-submitted' => array(
				'name'     => esc_html__( 'Listing successfully submitted.' ),
				'view'     => array( $this, 'success' ),
				'handler'  => false,
				'priority' => 30,
			),
		);

		/**
		 * List of steps for the listing submission form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the listing submission form.
		 */
		$this->steps = (array) apply_filters( 'pno_listing_submission_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( intval( $_POST['step'] ), array_keys( $this->steps ), true );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( intval( $_GET['step'] ), array_keys( $this->steps ), true );
		}

	}

	/**
	 * Initializes the fields used in the form.
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$fields = array(
			'listing-details' => pno_get_listing_submission_fields(),
		);

		$this->fields = $fields;

	}

	/**
	 * Display the listing type selection boxes.
	 *
	 * @return void
	 */
	public function type() {

		$this->init_fields();

		posterno()->templates
			->set_template_data(
				[
					'form'         => $this,
					'action'       => $this->get_action(),
					'fields'       => false,
					'step'         => $this->get_step(),
					'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
					'submit_label' => esc_html__( 'Continue' ),
				]
			)
			->get_template_part( 'forms/listing-type-selection' );

	}

	/**
	 * Save the selected listing type id and proceed to the next step.
	 *
	 * @return void
	 */
	public function type_handler() {
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

			$type_id = $this->get_submitted_listing_type_id();

			if ( $type_id ) {
				$this->listing_type_id = $type_id;
				$this->step ++;
			} else {
				throw new Exception( esc_html__( 'Something went wrong.' ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
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
			'submit_label' => esc_html__( 'Submit listing' ),
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
			 * Allow developers to extend the listing submission process.
			 * This action is fired before actually creating the new listing.
			 *
			 * @param array $values all the fields submitted through the form.
			 * @param object $this the class instance managing the form.
			 */
			do_action( 'pno_before_listing_submission', $values, $this );

			// Grab main listing details.
			$listing_title       = $values['listing_title'];
			$listing_description = isset( $values['listing_description'] ) ? $values['listing_description'] : '';
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
				if ( isset( $values['listing_email_address'] ) && ! empty( $values['listing_email_address'] ) ) {
					carbon_set_post_meta( $new_listing_id, 'listing_email', $values['listing_email_address'] );
				}
				if ( isset( $values['listing_phone_number'] ) && ! empty( $values['listing_phone_number'] ) ) {
					carbon_set_post_meta( $new_listing_id, 'listing_phone_number', $values['listing_phone_number'] );
				}
				if ( isset( $values['listing_website'] ) && ! empty( $values['listing_website'] ) ) {
					carbon_set_post_meta( $new_listing_id, 'listing_website', $values['listing_website'] );
				}
				if ( isset( $values['listing_video'] ) && ! empty( $values['listing_video'] ) ) {
					carbon_set_post_meta( $new_listing_id, 'listing_media_embed', $values['listing_video'] );
				}
				if ( isset( $values['listing_zipcode'] ) && ! empty( $values['listing_zipcode'] ) ) {
					carbon_set_post_meta( $new_listing_id, 'listing_zipcode', $values['listing_zipcode'] );
				}
				if ( isset( $values['listing_social_media_profiles'] ) && ! empty( $values['listing_social_media_profiles'] ) ) {
					pno_save_listing_social_profiles( $new_listing_id, $values['listing_social_media_profiles'] );
				}
				if ( isset( $values['listing_opening_hours'] ) && ! empty( $values['listing_opening_hours'] ) ) {
					pno_save_submitted_listing_opening_hours( $new_listing_id, $values['listing_opening_hours'] );
				}

				// Create a featured image for the listing.
				if ( isset( $values['listing_featured_image'] ) && ! empty( $values['listing_featured_image'] ) ) {
					$featured_image = isset( $values['listing_featured_image']['url'] ) ? $values['listing_featured_image']['url'] : $values['listing_featured_image'];
					if ( $featured_image ) {
						$attachment_id = $this->create_attachment( $new_listing_id, $featured_image );
						if ( $attachment_id ) {
							set_post_thumbnail( $new_listing_id, $attachment_id );
						}
					}
				}

				// Create images for the gallery.
				if ( isset( $values['listing_gallery'] ) && ! empty( $values['listing_gallery'] ) ) {
					$gallery_images = $values['listing_gallery'];
					if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
						$images_list = [];
						foreach ( $gallery_images as $uploaded_file ) {
							$gallery_image_url = isset( $uploaded_file['url'] ) ? $uploaded_file['url'] : $uploaded_file;
							$uploaded_file_id  = $this->create_attachment( $new_listing_id, $gallery_image_url );
							if ( $uploaded_file_id ) {
								$images_list[] = $uploaded_file_id;
							}
						}
						if ( ! empty( $images_list ) ) {
							carbon_set_post_meta( $new_listing_id, 'listing_gallery_images', $images_list );
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
					wp_set_object_terms( absint( $new_listing_id ), $listing_regions, 'listings-locations', true );
				}

				// Assign categories to the listing.
				if ( isset( $values['listing_categories'] ) && ! empty( $values['listing_categories'] ) ) {
					$listing_categories = json_decode( $values['listing_categories'] );
					$listing_categories = array_map( 'absint', $listing_categories );
					if ( ! empty( $listing_categories ) ) {
						wp_set_object_terms( absint( $new_listing_id ), array_unique( $listing_categories ), 'listings-categories' );
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
						wp_set_object_terms( absint( $new_listing_id ), $tags, 'listings-tags', true );
					}
				}

				// Assign the selected listing type to the listing.
				$listing_type = $this->get_submitted_listing_type_id();

				if ( ! empty( $listing_type ) ) {
					wp_set_object_terms( absint( $new_listing_id ), $listing_type, 'listings-types', true );
				}

				// Now update the custom fields that are not marked as default.
				foreach ( $this->fields['listing-details'] as $key => $field_details ) {
					if ( isset( $values[ $key ] ) && ! pno_is_default_field( $key ) ) {
						if ( $field_details['type'] === 'file' ) {

							$is_multiple = isset( $field_details['multiple'] ) && $field_details['multiple'] === true ? true : false;

							$attachments_uploaded = $values[ $key ];

							if ( $is_multiple ) {

								if ( is_array( $attachments_uploaded ) && ! empty( $attachments_uploaded ) ) {
									$new_attachments_list = [];
									foreach ( $attachments_uploaded as $uploaded_file ) {
										$new_attachment_url = isset( $uploaded_file['url'] ) ? $uploaded_file['url'] : $uploaded_file;
										if ( $new_attachment_url ) {
											$uploaded_file_id = $this->create_attachment( $new_listing_id, $new_attachment_url );
											if ( $uploaded_file_id ) {
												$new_attachments_list[] = $uploaded_file_id;
											}
										}
									}
									if ( ! empty( $new_attachments_list ) ) {
										carbon_set_post_meta( $new_listing_id, $key, $new_attachments_list );
									}
								}
							} else {

								if ( isset( $values[ $key ]['url'] ) ) {
									$attachment_id = $this->create_attachment( $new_listing_id, $values[ $key ]['url'] );
									if ( $attachment_id ) {
										carbon_set_post_meta( $new_listing_id, $key, $attachment_id );
									}
								}
							}
						} elseif ( in_array( $field_details['type'], [ 'term-select', 'term-multiselect', 'term-checklist', 'term-chain-dropdown' ] ) ) {

							$term_value = $values[ $key ];

							if ( $field_details['type'] === 'term-chain-dropdown' ) {
								$term_value = json_decode( $term_value );
							}

							if ( ! empty( $term_value ) ) {
								$this->process_taxonomy_field( $field_details, $new_listing_id, $term_value );
							}
						} elseif ( $field_details['type'] === 'checkbox' ) {

							if ( $values[ $key ] === '1' ) {
								carbon_set_post_meta( $new_listing_id, $key, true );
							}
						} else {

							if ( ! empty( $values[ $key ] ) ) {
								carbon_set_post_meta( $new_listing_id, $key, $values[ $key ] );
							}
						}
					}
				}

				/**
				 * Allow developers to extend the listing submission process.
				 * This action is fired after creating the new listing.
				 *
				 * @param array $values all the fields submitted through the form.
				 * @param string $new_listing_id the id number of the newly created listing.
				 * @param object $this the class instance managing the form.
				 */
				do_action( 'pno_after_listing_submission', $values, $new_listing_id, $this );

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
					 * @param array $values all the data submitted through the form.
					 * @return string
					 */
					$new_page_url = apply_filters( 'pno_successful_listing_submission_redirect_url', $new_page_url, $new_listing_id, $values );

					wp_safe_redirect( $new_page_url );
					exit;

				} else {

					$this->new_listing_id = $new_listing_id;
					$this->step ++;

				}
			}

			return;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Display the success message when no redirect is in place.
	 *
	 * @return void
	 */
	public function success() {

		$message = sprintf( __( 'Listing successfully submitted. <a href="%s">View your listing.</a>' ), get_permalink( $this->new_listing_id ) );

		if ( pno_listing_submission_is_moderated() ) {
			$message = esc_html__( 'Listing successfully submitted. Your listing will be visible once approved.' );
		}

		/**
		 * Allow developers to customize the message displayed after successfull listing submission.
		 *
		 * @param string $message the message that appears after listing submission.
		 */
		$success_message = apply_filters( 'pno_listing_submission_success_message', $message );

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'success',
					'message' => $message,
				]
			)
			->get_template_part( 'message' );

	}

}
