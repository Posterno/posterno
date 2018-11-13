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

		if (
			! is_user_logged_in() ||
			! is_page( pno_get_listing_editing_page_id() ) ||
			! $this->listing_id ||
			! pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) ||
			( pno_is_listing_pending_approval( $this->listing_id ) && pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) && ! pno_pending_listings_can_be_edited() ) ) {
			return $fields;
		}

		$submission_fields = pno_get_listing_submission_fields( $this->listing_id );

		foreach ( $submission_fields as $field_key => $the_field ) {

			// Get the field type so we can get the class name of the field.
			$field_type       = $the_field['type'];
			$field_type_class = $this->get_field_type_class_name( $field_type );

			// Define validation rules.
			$validation_rules = [];

			if ( isset( $the_field['required'] ) && $the_field['required'] === true ) {
				$validation_rules[] = new NotEmpty();
			}

			$field_options          = $this->get_field_options( $the_field );
			$field_options['rules'] = $validation_rules;

			$fields[] = new $field_type_class( $field_key, $field_options );

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

		if ( pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) && ! pno_is_listing_pending_approval( $this->listing_id ) || ( pno_is_listing_pending_approval( $this->listing_id ) && pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) && pno_pending_listings_can_be_edited() ) ) {

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

				/**
				 * Allow developers to extend the listing editing process.
				 * This action is fired before actually editing the listing.
				 *
				 * @param array $values all the fields submitted through the form.
				 * @param object $this the class instance managing the form.
				 */
				do_action( 'pno_before_listing_editing', $values, $this );

				$listing = array(
					'ID'           => $this->listing_id,
					'post_title'   => $values['listing_title'],
					'post_content' => $values['listing_description'],
					'post_status'  => $this->is_moderation_required(),
				);

				$updated_listing_id = wp_update_post( $listing );

				if ( is_wp_error( $updated_listing_id ) ) {
					throw new \Exception( $updated_listing_id->get_error_message() );
				} else {

					// Now manipulate the default fields data and store them if necessary.
					if ( isset( $values['listing_email_address'] ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_email', $values['listing_email_address'] );
					}
					if ( isset( $values['listing_phone_number'] ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_phone_number', $values['listing_phone_number'] );
					}
					if ( isset( $values['listing_website'] ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_website', $values['listing_website'] );
					}
					if ( isset( $values['listing_video'] ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_media_embed', $values['listing_video'] );
					}
					if ( isset( $values['listing_zipcode'] ) ) {
						carbon_set_post_meta( $updated_listing_id, 'listing_zipcode', $values['listing_zipcode'] );
					}

					if ( isset( $values['listing_social_media_profiles'] ) ) {
						pno_save_listing_social_profiles( $updated_listing_id, $values['listing_social_media_profiles'] );
					}

					if ( isset( $values['listing_opening_hours'] ) ) {
						pno_save_submitted_listing_opening_hours( $updated_listing_id, $values['listing_opening_hours'] );
					}

					// Assign terms.
					if ( isset( $values['listing_categories'] ) && ! empty( $values['listing_categories'] ) ) {
						$listing_categories = json_decode( $values['listing_categories'] );
						$categories_to_save = [];
						if ( isset( $listing_categories->parent ) && is_array( $listing_categories->parent ) && ! empty( $listing_categories->parent ) ) {
							foreach ( $listing_categories->parent as $term_id ) {
								$categories_to_save[] = absint( $term_id );
							}
						}
						if ( isset( $listing_categories->sub ) && is_array( $listing_categories->sub ) && ! empty( $listing_categories->sub ) ) {
							foreach ( $listing_categories->sub as $sub_term_id ) {
								$categories_to_save[] = absint( $sub_term_id );
							}
						}
						if ( ! empty( $categories_to_save ) ) {
							wp_set_object_terms( absint( $updated_listing_id ), $categories_to_save, 'listings-categories' );
						}
					}

					if ( isset( $values['listing_tags'] ) && ! empty( $values['listing_tags'] ) ) {
						$listing_tags = json_decode( $values['listing_tags'] );
						if ( is_array( $listing_tags ) ) {
							foreach ( $listing_tags as $tag ) {
								wp_set_object_terms( absint( $updated_listing_id ), absint( $tag->term_id ), 'listings-tags', true );
							}
						}
					}

					if ( isset( $values['listing_regions'] ) ) {
						$listing_region = $values['listing_regions'];
						$assign_parent  = pno_get_option( 'submission_region_sublevel', false );
						$new_regions    = [];
						if ( $assign_parent ) {
							$parent_region = pno_get_term_top_most_parent( $listing_region, 'listings-locations' );
							if ( isset( $parent_region->term_id ) ) {
								$new_regions[] = absint( $parent_region->term_id );
							}
						}
						$new_regions[] = absint( $listing_region );
						wp_set_object_terms( absint( $updated_listing_id ), $new_regions, 'listings-locations' );
					}

					// Update the featured image.
					if ( isset( $values['listing_featured_image'] ) && ! empty( $values['listing_featured_image'] ) ) {
						$attachment = json_decode( $values['listing_featured_image'] );
						if ( isset( $attachment->image_url ) ) {
							$attachment_id = $this->create_attachment( $updated_listing_id, $attachment->image_url );
							if ( $attachment_id ) {
								set_post_thumbnail( $updated_listing_id, $attachment_id );
							}
						}
					}

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

					/**
					 * Allow developers to extend the listing editing process.
					 * This action is fired after all the details of the listing have already been updated.
					 *
					 * @param array $values all the fields submitted through the form.
					 * @param string $updated_listing_id the id number of the newly created listing.
					 * @param object $this the class instance managing the form.
					 */
					do_action( 'pno_after_listing_editing', $values, $updated_listing_id, $this );

					// Now redirect the user.
					$redirect = pno_get_listing_success_edit_redirect_page_id();

					if ( $redirect ) {
						$redirect = get_permalink( $redirect );
					} else {
						$redirect = add_query_arg(
							[
								'message' => 'listing-updated',
							], pno_get_dashboard_navigation_item_url( 'listings' )
						);
					}

					/**
					 * Allow developers to adjust the url where members are redirected after
					 * successfully editing one of their listings.
					 *
					 * @param string $redirect the url to redirect to.
					 * @param array $values all the data submitted through the form.
					 * @param string|int $updated_listing_id the id of the listing that was updated.
					 * @param object $form the class instance managing the form.
					 * @return string
					 */
					$redirect = apply_filters( 'pno_listing_successful_editing_redirect_url', $redirect, $values, $updated_listing_id, $this );

					wp_safe_redirect( $redirect );
					exit;

				}
			}
		} catch ( \Exception $e ) {
			$this->form->set_processing_error( $e->getMessage() );
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
			return 'published';
		} else {
			return 'pending';
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
