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

			$fields[] = new $field_type_class(
				$field_key,
				[
					'label'       => $the_field['label'],
					'description' => isset( $the_field['description'] ) ? $the_field['description'] : false,
					'choices'     => isset( $the_field['options'] ) ? $the_field['options'] : false,
					'value'       => isset( $the_field['value'] ) ? $the_field['value'] : false,
					'required'    => (bool) $the_field['required'],
					'rules'       => $validation_rules,
					'attributes'  => $this->get_field_attributes( $the_field ),
					'taxonomy'    => isset( $the_field['taxonomy'] ) ? $the_field['taxonomy'] : false,
					'multiple'    => isset( $the_field['multiple'] ) ? $the_field['multiple'] : false,
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
			$step = 'submit_listing';
		} elseif ( ! empty( $_POST[ 'submit_' . $this->form->get_name() ] ) ) {
			$step = 'submit_listing';
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
		if ( isset( $_GET['listing_type'] ) && ! empty( $_GET['listing_type'] ) ) {
			$id = absint( $_GET['listing_type'] );
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

				/**
				 * Allow developers to extend the listing submisison process.
				 * This action is fired before actually creating the new listing.
				 *
				 * @param array $values all the fields submitted through the form.
				 * @param object $this the class instance managing the form.
				 */
				do_action( 'pno_before_listing_submission', $values, $this );

				// Grab main listing details.
				$listing_title       = $values['listing_title'];
				$listing_description = $values['listing_description'];
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
					throw new \Exception( $new_listing_id->get_error_message() );
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
						$attachment = json_decode( $values['listing_featured_image'] );
						if ( isset( $attachment[0] ) && isset( $attachment[0]->url ) ) {
							$attachment_id = $this->create_attachment( $new_listing_id, $attachment[0]->url );
							if ( $attachment_id ) {
								set_post_thumbnail( $new_listing_id, $attachment_id );
							}
						}
					}

					// Create images for the gallery.
					if ( isset( $values['listing_gallery'] ) && ! empty( $values['listing_gallery'] ) ) {
						$gallery_images = json_decode( $values['listing_gallery'] );
						if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
							$images_list = [];
							foreach ( $gallery_images as $uploaded_file ) {
								$uploaded_file_id = $this->create_attachment( $new_listing_id, $uploaded_file->url );
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
						$listing_region = $values['listing_regions'];
						$assign_parent  = pno_get_option( 'submission_region_sublevel', false );
						if ( $assign_parent ) {
							$parent_region = pno_get_term_top_most_parent( $listing_region, 'listings-locations' );
							if ( isset( $parent_region->term_id ) ) {
								wp_set_object_terms( absint( $new_listing_id ), absint( $parent_region->term_id ), 'listings-locations', true );
							}
						}
						wp_set_object_terms( absint( $new_listing_id ), absint( $listing_region ), 'listings-locations', true );
					}

					if ( isset( $values['listing_categories'] ) && ! empty( $values['listing_categories'] ) ) {
						$listing_categories = json_decode( $values['listing_categories'] );
						$use_sub_categories = pno_get_option( 'submission_categories_sublevel', false );
						if ( $use_sub_categories ) {
							foreach ( $listing_categories as $category ) {
								$parent_category = pno_get_term_top_most_parent( $category, 'listings-categories' );
								if ( isset( $parent_category->term_id ) ) {
									wp_set_object_terms( absint( $new_listing_id ), absint( $parent_category->term_id ), 'listings-categories', true );
								}
							}
						}
						foreach ( $listing_categories as $selected_category ) {
							wp_set_object_terms( absint( $new_listing_id ), absint( $selected_category ), 'listings-categories', true );
						}
					}

					var_dump( $new_listing_id );
					exit;

					/**
					 * Allow developers to extend the listing submission process.
					 * This action is fired after creating the new listing.
					 *
					 * @param array $values all the fields submitted through the form.
					 * @param string $new_listing_id the id number of the newly created listing.
					 * @param object $this the class instance managing the form.
					 */
					do_action( 'pno_after_listing_submission', $values, $new_listing_id, $this );

				}
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
