<?php
/**
 * Handles commonly used properties for all forms powered by Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Parent abstract class for form classes.
 *
 * @abstract
 * @package posterno
 * @since 0.1.0
 */
abstract class PNO_Form {

	/**
	 * Form fields.
	 *
	 * @access protected
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Form action.
	 *
	 * @access protected
	 * @var string
	 */
	protected $action = '';

	/**
	 * Form errors.
	 *
	 * @access protected
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Whether the form has been successful and a message has been assigned.
	 *
	 * @var boolean
	 */
	protected $success = false;

	/**
	 * Holds the success message assigned to the form.
	 *
	 * @var string
	 */
	protected $success_message = null;

	/**
	 * Form steps.
	 *
	 * @access protected
	 * @var array
	 */
	protected $steps = array();

	/**
	 * Current form step.
	 *
	 * @access protected
	 * @var int
	 */
	protected $step = 0;

	/**
	 * Form name.
	 *
	 * @access protected
	 * @var string
	 */
	public $form_name = '';

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__ );
	}

	/**
	 * Unserializes instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__ );
	}

	/**
	 * Processes the form result and can also change view if step is complete.
	 */
	public function process() {

		$step_key = $this->get_step_key( $this->step );

		if ( $step_key && is_callable( $this->steps[ $step_key ]['handler'] ) ) {
			call_user_func( $this->steps[ $step_key ]['handler'] );
		}

		$next_step_key = $this->get_step_key( $this->step );

		// If the next step has a handler to call before going to the view, run it now.
		if ( $next_step_key
			&& $step_key !== $next_step_key
			&& isset( $this->steps[ $next_step_key ]['before'] )
			&& is_callable( $this->steps[ $next_step_key ]['before'] )
		) {
			call_user_func( $this->steps[ $next_step_key ]['before'] );
		}

		// if the step changed, but the next step has no 'view', call the next handler in sequence.
		if ( $next_step_key && $step_key !== $next_step_key && ! is_callable( $this->steps[ $next_step_key ]['view'] ) ) {
			$this->process();
		}
	}

	/**
	 * Calls the view handler if set, otherwise call the next handler.
	 *
	 * @param array $atts Attributes to use in the view handler.
	 */
	public function output( $atts = array() ) {
		$step_key = $this->get_step_key( $this->step );
		if ( $step_key && is_callable( $this->steps[ $step_key ]['view'] ) ) {
			call_user_func( $this->steps[ $step_key ]['view'], $atts );
		}
	}

	/**
	 * Adds an error.
	 *
	 * @param string $error The error message.
	 * @param string $error_code option error code.
	 */
	public function add_error( $error, $error_code = false ) {
		if ( $error_code ) {
			$this->errors[ $error_code ] = $error;
		} else {
			$this->errors[] = $error;
		}
	}

	/**
	 * Retrieve all errors attached to the form.
	 *
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Determine if the form has errors.
	 *
	 * @return boolean
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Set the form as successful in order to show a message.
	 *
	 * @return void
	 */
	public function set_as_successful() {
		$this->success = true;
	}

	/**
	 * Assign a message to the form when successful.
	 *
	 * @param string $message the message to assign to the form.
	 * @return void
	 */
	public function set_success_message( $message ) {
		$this->success_message = $message;
	}

	/**
	 * Get the success message assigned to the form.
	 *
	 * @return string
	 */
	public function get_success_message() {
		return $this->success_message;
	}

	/**
	 * Verify if the form is successful.
	 *
	 * @return boolean
	 */
	public function is_successful() {
		return $this->success === true ? true : false;
	}

	/**
	 * Gets the action (URL for forms to post to).
	 *
	 * @return string
	 */
	public function get_action() {
		return esc_url_raw( $this->action ? $this->action : wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	/**
	 * Gets form name.
	 *
	 * @return string
	 */
	public function get_form_name() {
		return $this->form_name;
	}

	/**
	 * Gets steps from outside of the class.
	 *
	 * @return array
	 */
	public function get_steps() {
		return $this->steps;
	}

	/**
	 * Gets step from outside of the class.
	 *
	 * @return string
	 */
	public function get_step() {
		return $this->step;
	}

	/**
	 * Gets step key from outside of the class.
	 *
	 * @param string|int $step step key.
	 * @return string
	 */
	public function get_step_key( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}
		$keys = array_keys( $this->steps );
		return isset( $keys[ $step ] ) ? $keys[ $step ] : '';
	}

	/**
	 * Sets step from outside of the class.
	 *
	 * @param int $step step key.
	 */
	public function set_step( $step ) {
		$this->step = absint( $step );
	}

	/**
	 * Increases step from outside of the class.
	 */
	public function next_step() {
		$this->step ++;
	}

	/**
	 * Decreases step from outside of the class.
	 */
	public function previous_step() {
		$this->step --;
	}

	/**
	 * Gets fields for form.
	 *
	 * @param string $key key where fields have been added to.
	 * @return array
	 */
	public function get_fields( $key ) {
		if ( empty( $this->fields[ $key ] ) ) {
			return array();
		}

		$fields = $this->fields[ $key ];

		uasort( $fields, array( $this, 'sort_by_priority' ) );

		return $fields;
	}

	/**
	 * Sorts array by priority value.
	 *
	 * @param array $a a1.
	 * @param array $b a2.
	 * @return int
	 */
	protected function sort_by_priority( $a, $b ) {
		if ( floatval( $a['priority'] ) === floatval( $b['priority'] ) ) {
			return 0;
		}
		return ( floatval( $a['priority'] ) < floatval( $b['priority'] ) ) ? -1 : 1;
	}

	/**
	 * Initializes form fields.
	 */
	protected function init_fields() {
		$this->fields = array();
	}

	/**
	 * Gets post data for fields.
	 *
	 * @return array of data.
	 */
	protected function get_posted_fields() {
		$this->init_fields();

		$values = array();

		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				// Get the value.
				$field_type = str_replace( '-', '_', $field['type'] );
				$handler    = apply_filters( "pno_get_posted_{$field_type}_field", false );

				if ( $handler ) {
					$values[ $group_key ][ $key ] = call_user_func( $handler, $key, $field );
				} elseif ( method_exists( $this, "get_posted_{$field_type}_field" ) ) {
					$values[ $group_key ][ $key ] = call_user_func( array( $this, "get_posted_{$field_type}_field" ), $key, $field );
				} else {
					$values[ $group_key ][ $key ] = $this->get_posted_field( $key, $field );
				}

				// Set fields value.
				$this->fields[ $group_key ][ $key ]['value'] = $values[ $group_key ][ $key ];
			}
		}

		/**
		 * Alter values for posted fields.
		 *
		 * @param array  $values  The values that have been submitted.
		 * @param array  $fields  The form fields.
		 */
		return apply_filters( 'pno_get_posted_fields', $values, $this->fields );
	}

	/**
	 * Validates the posted fields.
	 *
	 * @param array $values values submitted through the form.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 * @throws Exception Uploaded file is not a valid mime-type or other validation error.
	 */
	protected function validate_fields( $values ) {
		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) ) {
					// translators: Placeholder %s is the label for the required field.
					return new WP_Error( 'validation-error', sprintf( __( '%s is a required field', 'posterno' ), $field['label'] ) );
				}
				if ( $field['required'] && $values[ $group_key ][ $key ] === '[]' && in_array( $field['type'], array( 'term-chain-dropdown', 'listing-category', 'listing-tags', 'social-profiles' ), true ) ) {
					return new WP_Error( 'validation-error', sprintf( __( '%s is a required field', 'posterno' ), $field['label'] ) );
				}
				if ( ! empty( $field['taxonomy'] ) && in_array( $field['type'], array( 'term-checklist', 'term-select', 'term-multiselect', 'term-chain-dropdown', 'listing-category', 'listing-tags' ), true ) ) {

					$term_value = json_decode( $values[ $group_key ][ $key ] );

					if ( is_array( $term_value ) ) {
						$check_value = $term_value;
					} else {
						$check_value = empty( $term_value ) ? array() : array( $term_value );
					}
					foreach ( $check_value as $term ) {
						if ( ! term_exists( absint( $term ), $field['taxonomy'] ) ) {
							// translators: Placeholder %s is the field label that is did not validate.
							return new WP_Error( 'validation-error', sprintf( __( '%s is invalid', 'posterno' ), $field['label'] ) );
						}
					}
				}
				if ( 'file' === $field['type'] ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						$check_value = array_filter( $values[ $group_key ][ $key ] );
					} else {
						$check_value = array_filter( array( $values[ $group_key ][ $key ] ) );
					}
					if ( ! empty( $check_value ) ) {
						foreach ( $check_value as $file_url ) {
							if ( is_numeric( $file_url ) ) {
								continue;
							}
							if ( is_array( $file_url ) && isset( $file_url['url'] ) ) {
								$file_url = $file_url['url'];
							}

							if ( isset( $field['allowed_mime_types'] ) && !empty( $field['allowed_mime_types'] ) ) {
								$file_url  = current( explode( '?', $file_url ) );
								$file_info = wp_check_filetype( $file_url );

								if ( ! is_numeric( $file_url ) && $file_info && ! in_array( $file_info['type'], $field['allowed_mime_types'], true ) ) {
									// translators: Placeholder %1$s is field label; %2$s is the file mime type; %3$s is the allowed mime-types.
									throw new Exception( sprintf( __( '"%1$s" (filetype %2$s) needs to be one of the following file types: %3$s', 'posterno' ), $field['label'], $file_info['ext'], implode( ', ', array_values( $field['allowed_mime_types'] ) ) ) );
								}
							}
							$file_url = esc_url( $file_url, array( 'http', 'https' ) );
							if ( empty( $file_url ) ) {
								throw new Exception( __( 'Invalid attachment provided.', 'posterno' ) );
							}
						}
					}
				}
			}
		}
		/**
		 * Allow developers to add custom validation rules within forms.
		 *
		 * @param boolean $pass whether validation is successful or not, set to false to trigger an error within the form.
		 * @param array $fields all fields assigned to the form.
		 * @param array $values all values submitted through the form.
		 * @param string $form_name the name of the form being processed.
		 */
		return apply_filters( 'pno_form_validate_fields', true, $this->fields, $values, $this->form_name );
	}

	/**
	 * Navigates through an array and sanitizes the field.
	 *
	 * @param array|string    $value      The array or string to be sanitized.
	 * @param string|callable $sanitizer  The sanitization method to use. Built in: `url`, `email`, `url_or_email`, or
	 *                                      default (text). Custom single argument callable allowed.
	 * @return array|string   $value      The sanitized array (or string from the callback).
	 */
	protected function sanitize_posted_field( $value, $sanitizer = null ) {
		// Sanitize value.
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				$value[ $key ] = $this->sanitize_posted_field( $val, $sanitizer );
			}

			return $value;
		}

		$value = trim( $value );

		if ( 'url' === $sanitizer ) {
			return esc_url_raw( $value );
		} elseif ( 'email' === $sanitizer ) {
			return sanitize_email( $value );
		} elseif ( 'url_or_email' === $sanitizer ) {
			if ( null !== wp_parse_url( $value, PHP_URL_HOST ) ) {
				// Sanitize as URL.
				return esc_url_raw( $value );
			}

			// Sanitize as email.
			return sanitize_email( $value );
		} elseif ( is_callable( $sanitizer ) ) {
			return call_user_func( $sanitizer, $value );
		}

		// Use standard text sanitizer.
		return sanitize_text_field( stripslashes( $value ) );
	}

	/**
	 * Gets the value of a posted field.
	 *
	 * @param  string $key field id.
	 * @param  array  $field field definition.
	 * @return string|array
	 */
	protected function get_posted_field( $key, $field ) {
		// Allow custom sanitizers with standard text fields.
		if ( ! isset( $field['sanitizer'] ) ) {
			$field['sanitizer'] = null;
		}
		return isset( $_POST[ $key ] ) ? $this->sanitize_posted_field( $_POST[ $key ], $field['sanitizer'] ) : '';
	}

	/**
	 * Gets the value of a posted multiselect field.
	 *
	 * @param  string $key field id.
	 * @param  array  $field field definition.
	 * @return array
	 */
	protected function get_posted_multiselect_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? array_map( 'sanitize_text_field', $_POST[ $key ] ) : array();
	}

	/**
	 * Gets the value of a posted file field.
	 *
	 * @param  string $key field id.
	 * @param  array  $field field definition.
	 *
	 * @return string|array
	 * @throws Exception When the upload fails.
	 */
	protected function get_posted_file_field( $key, $field ) {
		$file = $this->upload_file( $key, $field );

		if ( ! $file ) {
			$file = $this->get_posted_field( 'current_' . $key, $field );
		} elseif ( is_array( $file ) ) {
			$file = array_filter( array_merge( $file, (array) $this->get_posted_field( 'current_' . $key, $field ) ) );
		}

		return $file;
	}

	/**
	 * Gets the value of a posted textarea field.
	 *
	 * @param  string $key field id.
	 * @param  array  $field field definition.
	 * @return string
	 */
	protected function get_posted_textarea_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? wp_kses_post( trim( stripslashes( $_POST[ $key ] ) ) ) : '';
	}

	/**
	 * Gets the value of a posted textarea field.
	 *
	 * @param  string $key field id.
	 * @param  array  $field field definition.
	 * @return string
	 */
	protected function get_posted_editor_field( $key, $field ) {
		return $this->get_posted_textarea_field( $key, $field );
	}

	/**
	 * Gets posted terms for the taxonomy.
	 *
	 * @param  string $key field id.
	 * @param  array  $field field definition.
	 * @return array
	 */
	protected function get_posted_term_checklist_field( $key, $field ) {
		if ( isset( $_POST['tax_input'] ) && isset( $_POST['tax_input'][ $field['taxonomy'] ] ) ) {
			return array_map( 'absint', $_POST['tax_input'][ $field['taxonomy'] ] );
		} else {
			return array();
		}
	}

	/**
	 * Gets posted terms for the taxonomy.
	 *
	 * @param  string $key field id.
	 * @param  array  $field field definition.
	 * @return array
	 */
	protected function get_posted_term_multiselect_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? array_map( 'absint', $_POST[ $key ] ) : array();
	}

	/**
	 * Gets posted terms for the taxonomy.
	 *
	 * @param  string $key field id.
	 * @param  array  $field field definition.
	 * @return int
	 */
	protected function get_posted_term_select_field( $key, $field ) {
		return ! empty( $_POST[ $key ] ) && $_POST[ $key ] > 0 ? absint( $_POST[ $key ] ) : '';
	}

	/**
	 * Handles the uploading of files.
	 *
	 * @param  string $field_key field id.
	 * @param  array  $field field definition.
	 * @throws Exception When file upload failed.
	 * @return  string|array
	 */
	protected function upload_file( $field_key, $field ) {
		if ( isset( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ]['name'] ) ) {
			if ( ! empty( $field['allowed_mime_types'] ) ) {
				$allowed_mime_types = $field['allowed_mime_types'];
			} else {
				$allowed_mime_types = pno_get_allowed_mime_types();
			}

			$file_urls       = array();
			$files_to_upload = pno_prepare_uploaded_files( $_FILES[ $field_key ] );

			foreach ( $files_to_upload as $file_to_upload ) {
				$uploaded_file = pno_upload_file(
					$file_to_upload,
					array(
						'file_key'           => $field_key,
						'allowed_mime_types' => $allowed_mime_types,
						'max_size'           => isset( $field['max_size'] ) ? $field['max_size'] : false,
					)
				);

				if ( is_wp_error( $uploaded_file ) ) {
					throw new Exception( $uploaded_file->get_error_message() );
				} else {
					$file_urls[] = [
						'url'  => $uploaded_file->url,
						'path' => $uploaded_file->file,
					];
				}
			}

			if ( ! empty( $field['multiple'] ) ) {
				return $file_urls;
			} else {
				return current( $file_urls );
			}
		}
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
	 * Reset fields back to empty.
	 *
	 * @return void
	 */
	protected function unbind() {
		$this->fields = [];
	}

	/**
	 * Assign to the listing the taxonomies selected through the field.
	 *
	 * @param array        $field_details details about the field being submitted.
	 * @param string       $listing_id the listing to update.
	 * @param array|string $values the terms to assign.
	 * @return void
	 */
	protected function process_taxonomy_field( $field_details, $listing_id, $values ) {

		if ( ! $values || ! $listing_id ) {
			return;
		}

		$taxonomy = isset( $field_details['taxonomy'] ) ? sanitize_text_field( $field_details['taxonomy'] ) : false;

		if ( ! $taxonomy ) {
			return;
		}

		$belongs_to_listings = false;

		$registered_taxonomies = get_object_taxonomies( 'listings', 'objects' );
		$listing_taxonomies    = [];

		foreach ( $registered_taxonomies as $tax => $details ) {
			$listing_taxonomies[] = $tax;
		}

		$terms = is_array( $values ) ? array_map( 'absint', $values ) : absint( $values );

		if ( in_array( $taxonomy, $listing_taxonomies ) ) {
			wp_set_object_terms( absint( $listing_id ), $terms, $taxonomy, false );
		}

	}

	/**
	 * Detect if a listing type has been selected and retrieve it's id.
	 *
	 * @return mixed
	 */
	public function get_submitted_listing_type_id() {

		$id = false;

		//phpcs:ignore
		if ( isset( $_POST['listing_type_id'] ) && ! empty( $_POST['listing_type_id'] ) ) {
			$id = absint( $_POST['listing_type_id'] );
		}

		return $id;

	}

	/**
	 * Validate the honeypot field.
	 *
	 * @param boolean $pass pass validation or not.
	 * @param array   $fields all fields belonging to the form.
	 * @param array   $values values sent through the form.
	 * @param string  $form form's name.
	 * @return mixed
	 */
	public function validate_honeypot( $pass, $fields, $values, $form ) {
		if ( $form === $this->form_name && isset( $values[ $this->form_name ]['robo'] ) ) {
			if ( ! empty( $values[ $this->form_name ]['robo'] ) ) {
				return new WP_Error( 'honeypot-validation-error', esc_html__( 'Failed honeypot validation.', 'posterno' ) );
			}
		}
		return $pass;
	}

}
