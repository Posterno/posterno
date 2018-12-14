<?php
/**
 * List of functions that handle file uploads within the fronted.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Prepares files for upload by standardizing them into an array. This adds support for multiple file upload fields.
 *
 * @since 0.1.0
 * @param  array $file_data
 * @return array
 */
function pno_prepare_uploaded_files( $file_data ) {
	$files_to_upload = array();
	if ( is_array( $file_data['name'] ) ) {
		foreach ( $file_data['name'] as $file_data_key => $file_data_value ) {
			if ( $file_data['name'][ $file_data_key ] ) {
				$type              = wp_check_filetype( $file_data['name'][ $file_data_key ] ); // Map mime type to one WordPress recognises.
				$files_to_upload[] = array(
					'name'     => $file_data['name'][ $file_data_key ],
					'type'     => $type['type'],
					'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
					'error'    => $file_data['error'][ $file_data_key ],
					'size'     => $file_data['size'][ $file_data_key ],
				);
			}
		}
	} else {
		$type              = wp_check_filetype( $file_data['name'] ); // Map mime type to one WordPress recognises.
		$file_data['type'] = $type['type'];
		$files_to_upload[] = $file_data;
	}
	return apply_filters( 'pno_prepare_uploaded_files', $files_to_upload );
}

/**
 * Uploads a file using WordPress file API.
 *
 * @since  0.1.0
 * @param  array|WP_Error      $file Array of $_FILE data to upload.
 * @param  string|array|object $args Optional arguments.
 * @return stdClass|WP_Error Object containing file information, or error.
 */
function pno_upload_file( $file, $args = array() ) {

	global $pno_upload, $pno_uploading_file;

	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/media.php';

	$args               = wp_parse_args(
		$args,
		array(
			'file_key'           => '',
			'file_label'         => '',
			'allowed_mime_types' => '',
			'max_size'           => '',
		)
	);
	$pno_upload         = true;
	$pno_uploading_file = $args['file_key'];
	$uploaded_file      = new stdClass();
	if ( '' === $args['allowed_mime_types'] ) {
		$allowed_mime_types = pno_get_allowed_mime_types( $pno_uploading_file );
	} else {
		$allowed_mime_types = $args['allowed_mime_types'];
	}

	/**
	 * Filter file configuration before upload
	 *
	 * This filter can be used to modify the file arguments before being uploaded, or return a WP_Error
	 * object to prevent the file from being uploaded, and return the error.
	 *
	 * @since 0.1.0
	 *
	 * @param array $file               Array of $_FILE data to upload.
	 * @param array $args               Optional file arguments.
	 * @param array $allowed_mime_types Array of allowed mime types from field config or defaults.
	 */
	$file = apply_filters( 'pno_upload_file_pre_upload', $file, $args, $allowed_mime_types );

	if ( is_wp_error( $file ) ) {
		return $file;
	}

	$file_extension = wp_check_filetype( $file['name'] );

	if ( isset( $args['max_size'] ) && ! empty( $args['max_size'] ) ) {
		$file_size = $file['size'];
		$max_size  = absint( $args['max_size'] );
		if ( $file_size > $max_size ) {
			return new WP_Error( 'upload', sprintf( __( 'Uploaded file "%1$s" exceeds the maximum file size of: %2$s' ), $file['name'], size_format( $max_size ) ) );
		}
	}

	if ( is_array( $file_extension ) && isset( $file_extension['type'] ) && ! in_array( $file_extension['type'], $allowed_mime_types ) ) {
		return new WP_Error( 'upload', sprintf( __( 'Uploaded file "%1$s" needs to be one of the following file types: %2$s' ), $file['name'], implode( ', ', array_values( $allowed_mime_types ) ) ) );
	} else {
		$upload = wp_handle_upload( $file, apply_filters( 'submit_pno_handle_upload_overrides', array( 'test_form' => false ) ) );
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'] );
		} else {
			$uploaded_file->url       = $upload['url'];
			$uploaded_file->file      = $upload['file'];
			$uploaded_file->name      = basename( $upload['file'] );
			$uploaded_file->type      = $upload['type'];
			$uploaded_file->size      = $file['size'];
			$uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
		}
	}
	$pno_upload         = false;
	$pno_uploading_file = '';
	return $uploaded_file;
}

/**
 * Returns mime types specifically for PNO.
 *
 * @since   0.1.0
 * @param   string $field Field used.
 * @return  array  Array of allowed mime types
 */
function pno_get_allowed_mime_types( $field = '' ) {
	if ( 'avatar' === $field ) {
		$allowed_mime_types = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
		);
	} else {
		$allowed_mime_types = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'pdf'          => 'application/pdf',
			'doc'          => 'application/msword',
			'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);
	}
	/**
	 * Mime types to accept in uploaded files.
	 *
	 * Default is image, pdf, and doc(x) files.
	 *
	 * @param array  {
	 *     Array of allowed file extensions and mime types.
	 *     Key is pipe-separated file extensions. Value is mime type.
	 * }
	 * @param string $field The field key for the upload.
	 */
	return apply_filters( 'pno_mime_types', $allowed_mime_types, $field );
}

/**
 * Wrapper function for size_format - checks the max size for file fields.
 *
 * @param string $custom_size in bytes.
 * @return string
 */
function pno_max_upload_size( $custom_size = false ) {
	// Default max upload size.
	$output = size_format( wp_max_upload_size() );

	if ( $custom_size ) {
		$output = size_format( intval( $custom_size ), 0 );
	}
	return $output;
}
