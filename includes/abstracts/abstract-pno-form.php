<?php
/**
 * Main class that handles all frontend forms of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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

		// reset cookie.
		if (
			isset( $_GET['new'] ) &&
			isset( $_COOKIE['pno-submitting-id'] ) &&
			isset( $_COOKIE['pno-submitting-key'] ) &&
			get_post_meta( $_COOKIE['pno-submitting-id'], '_submitting_key', true ) === $_COOKIE['pno-submitting-key']
		) {
			delete_post_meta( $_COOKIE['pno-submitting-id'], '_submitting_key' );
			setcookie( 'pno-submitting-id', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
			setcookie( 'pno-submitting-key', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
			wp_redirect( remove_query_arg( array( 'new', 'key' ), $_SERVER['REQUEST_URI'] ) );
		}

		$step_key = $this->get_step_key( $this->step );

		if ( $step_key && is_callable( $this->steps[ $step_key ]['handler'] ) ) {
			call_user_func( $this->steps[ $step_key ]['handler'] );
		}

		$next_step_key = $this->get_step_key( $this->step );

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
		$this->show_errors();

		if ( $step_key && is_callable( $this->steps[ $step_key ]['view'] ) ) {
			call_user_func( $this->steps[ $step_key ]['view'], $atts );
		}
	}

	/**
	 * Adds an error.
	 *
	 * @param string $error The error message.
	 */
	public function add_error( $error ) {
		$this->errors[] = $error;
	}

	/**
	 * Displays errors.
	 */
	public function show_errors() {
		foreach ( $this->errors as $error ) {
			echo '<div class="alert alert-danger" role="alert">' . wp_kses_post( $error ) . '</div>';
		}
	}

	/**
	 * Gets the action (URL for forms to post to).
	 * As of 1.22.2 this defaults to the current page permalink.
	 *
	 * @return string
	 */
	public function get_action() {
		return esc_url_raw( $this->action ? $this->action : wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	/**
	 * Gets form name.
	 *
	 * @since 1.24.0
	 * @return string
	 */
	public function get_form_name() {
		return $this->form_name;
	}

	/**
	 * Gets steps from outside of the class.
	 *
	 * @since 1.24.0
	 */
	public function get_steps() {
		return $this->steps;
	}

	/**
	 * Gets step from outside of the class.
	 */
	public function get_step() {
		return $this->step;
	}

	/**
	 * Gets step key from outside of the class.
	 *
	 * @since 1.24.0
	 * @param string|int $step
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
	 * @since 1.24.0
	 * @param int $step
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
	 * @param string $key
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
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	protected function sort_by_priority( $a, $b ) {
		if ( intval( $a['priority'] ) === intval( $b['priority'] ) ) {
			return 0;
		}
		return ( intval( $a['priority'] ) < intval( $b['priority'] ) ) ? -1 : 1;
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

		return $values;
	}

	/**
	 * Navigates through an array and sanitizes the field.
	 *
	 * @since 1.22.0
	 * @since 1.29.1 Added the $sanitizer argument
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
	 * @param  string $key
	 * @param  array  $field
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
	 * @param  string $key
	 * @param  array  $field
	 * @return array
	 */
	protected function get_posted_multiselect_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? array_map( 'sanitize_text_field', $_POST[ $key ] ) : array();
	}

	/**
	 * Gets the value of a posted file field.
	 *
	 * @param  string $key
	 * @param  array  $field
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
	 * @param  string $key
	 * @param  array  $field
	 * @return string
	 */
	protected function get_posted_textarea_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? wp_kses_post( trim( stripslashes( $_POST[ $key ] ) ) ) : '';
	}

	/**
	 * Gets the value of a posted textarea field.
	 *
	 * @param  string $key
	 * @param  array  $field
	 * @return string
	 */
	protected function get_posted_wp_editor_field( $key, $field ) {
		return $this->get_posted_textarea_field( $key, $field );
	}

	/**
	 * Gets posted terms for the taxonomy.
	 *
	 * @param  string $key
	 * @param  array  $field
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
	 * @param  string $key
	 * @param  array  $field
	 * @return array
	 */
	protected function get_posted_term_multiselect_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? array_map( 'absint', $_POST[ $key ] ) : array();
	}

	/**
	 * Gets posted terms for the taxonomy.
	 *
	 * @param  string $key
	 * @param  array  $field
	 * @return int
	 */
	protected function get_posted_term_select_field( $key, $field ) {
		return ! empty( $_POST[ $key ] ) && $_POST[ $key ] > 0 ? absint( $_POST[ $key ] ) : '';
	}

	/**
	 * Handles the uploading of files.
	 *
	 * @param string $field_key
	 * @param array  $field
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

				if ( isset( $field['max_size'] ) && ! empty( $field['max_size'] ) && isset( $file_to_upload['size'] ) ) {
					$uploaded_file_size = $file_to_upload['size'];
					if ( $uploaded_file_size > $field['max_size'] ) {
						$error = sprintf( esc_html__( '%s exceeds the maximum upload size.' ), '<strong>' . $file_to_upload['name'] . '</strong>' );
						throw new Exception( $error );
					}
				}

				$uploaded_file = pno_upload_file(
					$file_to_upload,
					array(
						'file_key'           => $field_key,
						'allowed_mime_types' => $allowed_mime_types,
					)
				);

				if ( is_wp_error( $uploaded_file ) ) {
					throw new Exception( $uploaded_file->get_error_message() );
				} else {
					$file_urls[] = [
						'url'  => $uploaded_file->url,
						'path' => $uploaded_file->file
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
	 * Validates the posted fields.
	 *
	 * @param array $values
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 * @throws Exception Uploaded file is not a valid mime-type or other validation error.
	 */
	protected function validate_fields( $values ) {
		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) ) {
					// translators: Placeholder %s is the label for the required field.
					return new WP_Error( 'validation-error', sprintf( __( '%s is a required field' ), $field['label'] ) );
				}
				if ( ! empty( $field['taxonomy'] ) && in_array( $field['type'], array( 'term-checklist', 'term-select', 'term-multiselect' ), true ) ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						$check_value = $values[ $group_key ][ $key ];
					} else {
						$check_value = empty( $values[ $group_key ][ $key ] ) ? array() : array( $values[ $group_key ][ $key ] );
					}
					foreach ( $check_value as $term ) {
						if ( ! term_exists( $term, $field['taxonomy'] ) ) {
							// translators: Placeholder %s is the field label that is did not validate.
							return new WP_Error( 'validation-error', sprintf( __( '%s is invalid' ), $field['label'] ) );
						}
					}
				}
				if ( 'file' === $field['type'] && ! empty( $field['allowed_mime_types'] ) ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						$check_value = array_filter( $values[ $group_key ][ $key ] );
					} else {
						$check_value = array_filter( array( $values[ $group_key ][ $key ] ) );
					}
					if ( ! empty( $check_value ) ) {
						foreach ( $check_value as $file_url ) {
							$file_url  = current( explode( '?', $file_url ) );
							$file_info = wp_check_filetype( $file_url );

							if ( ! is_numeric( $file_url ) && $file_info && ! in_array( $file_info['type'], $field['allowed_mime_types'], true ) ) {
								// translators: Placeholder %1$s is field label; %2$s is the file mime type; %3$s is the allowed mime-types.
								throw new Exception( sprintf( __( '"%1$s" (filetype %2$s) needs to be one of the following file types: %3$s' ), $field['label'], $file_info['ext'], implode( ', ', array_keys( $field['allowed_mime_types'] ) ) ) );
							}
						}
					}
				}
			}
		}

		return apply_filters( 'pno_form_validate_fields', true, $this->fields, $values, $this->get_form_name() );
	}

}
