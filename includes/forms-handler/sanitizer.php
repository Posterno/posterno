<?php
/**
 * Responsible of sanitizing submissions through a PNO\Form before validation.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form;

use PNO\Form\Field\AbstractField;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Responsible of sanitizing content of submitted fields through a PNO\Form.
 */
class Sanitizer {

	/**
	 * Sanitize the value of a field based on it's type.
	 *
	 * @param AbstractField $field the field we're going to sanitize.
	 * @return AbstractField
	 */
	public static function sanitize_field( AbstractField $field ) {

		$field_type = str_replace( '-', '_', $field->get_type() );
		$handler    = apply_filters( "pno_get_posted_{$field_type}_field", false );
		$value      = null;

		if ( $handler ) {
			$value = call_user_func( $handler, $field );
		} elseif ( method_exists( __class__, "get_posted_{$field_type}_field" ) ) {
			$value = call_user_func( array( __class__, "get_posted_{$field_type}_field" ), $field );
		} else {
			$value = self::get_posted_field( $field );
		}

		$field->set_value( $value );

		return $field;

	}

	/**
	 * Gets the value of a posted field.
	 *
	 * @param AbstractField $field the field we're going to sanitize.
	 * @return mixed
	 */
	protected static function get_posted_field( AbstractField $field ) {

		// Allow custom sanitizers with standard text fields.
		if ( ! $field->get_option( 'sanitizer' ) ) {
			$field->add_option( 'sanitizer', null );
		}

		return isset( $_POST[ $field->get_parent()->get_name() ][ $field->get_id() ] ) ? self::sanitize_posted_field( $field ) : ''; //phpcs:ignore
	}

	/**
	 * Sanitizes the field.
	 *
	 * @param AbstractField $field the field to sanitize.
	 * @return array|string $value the sanitized array (or string from the callback).
	 */
	protected static function sanitize_posted_field( AbstractField $field ) {

		$value     = $field->get_value();
		$sanitizer = $field->get_option( 'sanitizer' );

		// Sanitize value.
		if ( is_array( $value ) ) {
			$values = [];
			foreach ( $value as $key => $val ) {
				$values[ $key ] = sanitize_text_field( $val );
			}
			return $values;
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
	 * Gets the value of a posted multiselect field.
	 *
	 * @param AbstractField $field the field to sanitize.
	 * @return array
	 */
	protected static function get_posted_multiselect_field( AbstractField $field ) {
		 //phpcs:ignore
		return isset( $_POST[ $field->get_parent()->get_name() ][ $field->get_id() ] ) ? array_map( 'sanitize_text_field', $field->get_value() ) : array();
	}

	/**
	 * Gets the value of a posted multicheckboxes field.
	 *
	 * @param AbstractField $field the field to sanitize.
	 * @return array
	 */
	protected static function get_posted_multicheckbox_field( AbstractField $field ) {
		return self::get_posted_multiselect_field( $field );
	}

	/**
	 * Gets the value of a posted textarea field.
	 *
	 * @param AbstractField $field the field to sanitize.
	 * @return string
	 */
	protected static function get_posted_textarea_field( AbstractField $field ) {
		//phpcs:ignore
		return isset( $_POST[ $field->get_parent()->get_name() ][ $field->get_id() ] ) ? wp_kses_post( trim( stripslashes( $field->get_value() ) ) ) : '';
	}

	/**
	 * Gets the value of a posted editor field.
	 *
	 * @param AbstractField $field the field to sanitize.
	 * @return string
	 */
	protected static function get_posted_editor_field( AbstractField $field ) {
		return self::get_posted_textarea_field( $field );
	}

	/**
	 * Gets the value of a posted file field.
	 *
	 * @param AbstractField $field the field to sanitize.
	 *
	 * @return string|array
	 * @throws \Exception When the upload fails.
	 */
	protected static function get_posted_file_field( AbstractField $field ) {
		$file = self::upload_file( $field );

		if ( ! $file ) {
			$file = self::get_posted_field( $field );
		} elseif ( is_array( $file ) ) {
			$file = array_filter( array_merge( $file, (array) self::get_posted_field( $field ) ) );
		}

		return $file;
	}

	/**
	 * Handles the uploading of files.
	 *
	 * @param AbstractField $field the field to sanitize.
	 * @throws \Exception When file upload failed.
	 * @return  string|array
	 */
	protected static function upload_file( AbstractField $field ) {

		if ( isset( $_FILES[ $field->get_id() ] ) && ! empty( $_FILES[ $field->get_id() ] ) && ! empty( $_FILES[ $field->get_id() ]['name'] ) ) {
			if ( ! empty( $field->get_option( 'allowed_mime_types' ) ) ) {
				$allowed_mime_types = $field->get_option( 'allowed_mime_types' );
			} else {
				$allowed_mime_types = pno_get_allowed_mime_types();
			}

			$file_urls       = array();
			$files_to_upload = pno_prepare_uploaded_files( $_FILES[ $field->get_id() ] );

			foreach ( $files_to_upload as $file_to_upload ) {

				if ( $field->get_option( 'max_size' ) && isset( $file_to_upload['size'] ) ) {
					$uploaded_file_size = $file_to_upload['size'];
					if ( $uploaded_file_size > $field->get_option( 'max_size' ) ) {
						$error = sprintf( esc_html__( '%s exceeds the maximum upload size.' ), '<strong>' . $file_to_upload['name'] . '</strong>' );
						throw new \Exception( $error );
					}
				}

				$uploaded_file = pno_upload_file(
					$file_to_upload,
					array(
						'file_key'           => $field->get_id(),
						'allowed_mime_types' => $allowed_mime_types,
					)
				);

				if ( is_wp_error( $uploaded_file ) ) {
					throw new \Exception( $uploaded_file->get_error_message() );
				} else {
					$file_urls[] = [
						'url'  => $uploaded_file->url,
						'path' => $uploaded_file->file
					];
				}
			}

			if ( ! empty( $field->get_option( 'multiple' ) ) ) {
				return $file_urls;
			} else {
				return current( $file_urls );
			}
		}
	}

	/* For later use.
	protected function get_posted_term_checklist_field( $key, $field ) {
		if ( isset( $_POST['tax_input'] ) && isset( $_POST['tax_input'][ $field['taxonomy'] ] ) ) {
			return array_map( 'absint', $_POST['tax_input'][ $field['taxonomy'] ] );
		} else {
			return array();
		}
	}

	protected function get_posted_term_multiselect_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? array_map( 'absint', $_POST[ $key ] ) : array();
	}

	protected function get_posted_term_select_field( $key, $field ) {
		return ! empty( $_POST[ $key ] ) && $_POST[ $key ] > 0 ? absint( $_POST[ $key ] ) : '';
	}*/

}
