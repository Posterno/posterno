<?php
/**
 * Abstract representation of a PNO\Forms.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

use PNO\Form;
use PNO\Form\Field\AbstractField;

/**
 * Abstract definition of a Posterno's frontend form.
 */
abstract class Forms {

	/**
	 * Holds the definition of the form.
	 *
	 * @var Form
	 */
	protected $form;

	/**
	 * The name of the form. Unique string no spaces.
	 *
	 * @var string
	 */
	public $form_name = '';

	/**
	 * Label of the submission form.
	 *
	 * @var string
	 */
	public $submit_label = '';

	/**
	 * The object type the PNO\Form is going to work with.
	 *
	 * @var string
	 */
	public $object_type = '';

	/**
	 * Get things started.
	 */
	public function __construct() {

		if ( ! empty( $this->form_name ) && ! empty( $this->submit_label ) ) {
			$this->setup_form();
		}

	}

	/**
	 * Setup the form object.
	 *
	 * @return void
	 */
	private function setup_form() {
		$this->form = new Form( $this->form_name, $this->get_fields(), $this->object_type );
	}

	/**
	 * Get fields definition for the form.
	 *
	 * @return void
	 */
	abstract public function get_fields();

	/**
	 * Process the form's submission.
	 *
	 * @return void
	 */
	abstract public function process();

	/**
	 * Get the class name of a given field type.
	 *
	 * @param string $type the defined's field type.
	 * @return string
	 */
	protected function get_field_type_class_name( $type ) {

		$field_type_class = '\PNO\Form\Field\TextField';

		switch ( $type ) {
			case 'password':
				$field_type_class = '\PNO\Form\Field\PasswordField';
				break;
			case 'textarea':
				$field_type_class = '\PNO\Form\Field\TextAreaField';
				break;
			case 'editor':
				$field_type_class = '\PNO\Form\Field\EditorField';
				break;
			case 'email':
				$field_type_class = '\PNO\Form\Field\EmailField';
				break;
			case 'checkbox':
				$field_type_class = '\PNO\Form\Field\CheckboxField';
				break;
			case 'select':
				$field_type_class = '\PNO\Form\Field\DropdownField';
				break;
			case 'multiselect':
				$field_type_class = '\PNO\Form\Field\MultiSelectField';
				break;
			case 'multicheckbox':
				$field_type_class = '\PNO\Form\Field\MultiCheckboxField';
				break;
			case 'number':
				$field_type_class = '\PNO\Form\Field\NumberField';
				break;
			case 'radio':
				$field_type_class = '\PNO\Form\Field\RadioField';
				break;
			case 'url':
				$field_type_class = '\PNO\Form\Field\URLField';
				break;
			case 'file':
				$field_type_class = '\PNO\Form\Field\FileField';
				break;
			case 'social-profiles':
				$field_type_class = '\PNO\Form\Field\SocialProfilesField';
				break;
			case 'listing-category':
				$field_type_class = '\PNO\Form\Field\ListingCategoryField';
				break;
			case 'listing-tags':
				$field_type_class = '\PNO\Form\Field\ListingTagsField';
				break;
			case 'term-select':
				$field_type_class = '\PNO\Form\Field\TermSelectField';
				break;
			case 'opening-hours':
				$field_type_class = '\PNO\Form\Field\ListingOpeningHoursField';
				break;
			case 'dropzone':
				$field_type_class = '\PNO\Form\Field\DropzoneField';
				break;
			case 'listing-location':
				$field_type_class = '\PNO\Form\Field\ListingLocationField';
				break;
		}

		/**
		 * Allow developers to define the class name of custom fields if any.
		 *
		 * @param string $field_type_class the class name to return in order to instantiate the field.
		 * @param string $type retrieved field type string.
		 */
		return apply_filters( 'pno_forms_field_class_name', $field_type_class, $type );

	}

	/**
	 * Get attributes assigned to the field.
	 *
	 * @param array $field the field we're going to work with.
	 * @return array
	 */
	protected function get_field_attributes( $field ) {

		$attributes = [];

		if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
			$attributes = $field['attributes'];
		}

		if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) {
			$attributes['placeholder'] = $field['placeholder'];
		}

		/**
		 * Allow developers to customize the attributes assigned to a field.
		 *
		 * @param array $attributes the list of attributes if any.
		 * @param array $field the field being analyzed.
		 * @return array
		 */
		return apply_filters( 'pno_field_attributes', $attributes, $field );

	}

	/**
	 * Get a list of all possible options that are sent through fields.
	 *
	 * @param array $field the field we're working with.
	 * @return array
	 */
	protected function get_field_options( $field ) {

		return [
			'label'              => $field['label'],
			'description'        => isset( $field['description'] ) ? $field['description'] : false,
			'choices'            => isset( $field['options'] ) ? $field['options'] : false,
			'value'              => isset( $field['value'] ) ? $field['value'] : false,
			'required'           => (bool) $field['required'],
			'attributes'         => $this->get_field_attributes( $field ),
			'taxonomy'           => isset( $field['taxonomy'] ) ? $field['taxonomy'] : false,
			'multiple'           => isset( $field['multiple'] ) ? $field['multiple'] : false,
			'dropzone_max_files' => isset( $field['dropzone_max_files'] ) ? $field['dropzone_max_files'] : false,
			'dropzone_max_size'  => isset( $field['dropzone_max_size'] ) ? $field['dropzone_max_size'] : false,
		];

	}

	/**
	 * Create a file attachment for a listing.
	 *
	 * @param string $listing_id the id number of the listing for which we're creating the attachment.
	 * @param string $attachment_url attachment url.
	 * @return string|boolean
	 */
	protected function create_attachment( $listing_id, $attachment_url ) {

		include_once ABSPATH . 'wp-admin/includes/image.php';
		include_once ABSPATH . 'wp-admin/includes/media.php';

		$upload_dir     = wp_upload_dir();
		$attachment_url = esc_url( $attachment_url, array( 'http', 'https' ) );

		if ( empty( $attachment_url ) ) {
			return false;
		}

		$attachment_url = str_replace( array( $upload_dir['baseurl'], WP_CONTENT_URL, site_url( '/' ) ), array( $upload_dir['basedir'], WP_CONTENT_DIR, ABSPATH ), $attachment_url );

		if ( empty( $attachment_url ) || ! is_string( $attachment_url ) ) {
			return false;
		}

		$attachment = array(
			'post_title'   => pno_get_the_listing_title( $listing_id ),
			'post_content' => '',
			'post_status'  => 'inherit',
			'post_parent'  => $listing_id,
			'guid'         => $attachment_url,
		);

		$info = wp_check_filetype( $attachment_url );
		if ( $info ) {
			$attachment['post_mime_type'] = $info['type'];
		}

		$attachment_id = wp_insert_attachment( $attachment, $attachment_url, $listing_id );

		if ( ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $attachment_url ) );
			return $attachment_id;
		}

		return false;

	}

	/**
	 * Process a dropzone submitted content.
	 *
	 * @param string $key the name of the field being processed.
	 * @param string $value the value submitted through the dropzone.
	 * @param string $object_id the id number of the object where we're going to attach files.
	 * @param string $object_type the type of data object we're going to work with: post_meta, user_meta.
	 * @return void
	 */
	protected function process_dropzone( $key, $value, $object_id, $object_type = 'post_meta' ) {

		if ( empty( $value ) || ! $object_id || ! $object_type ) {
			return;
		}

		$files = json_decode( $value );

		if ( is_array( $files ) && ! empty( $files ) ) {

			$files_to_return = [];

			foreach ( $files as $uploaded_file ) {
				$uploaded_file_id = $this->create_attachment( $updated_listing_id, $uploaded_file->image_url );
				if ( $uploaded_file_id ) {
					$files_to_return[] = $uploaded_file_id;
				}
			}

			return $files_to_return;

		} else {
			if ( isset( $files->image_url ) ) {
				return $this->create_attachment( $object_id, $files->image_url );
			}
		}

	}

}
