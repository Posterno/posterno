<?php
/**
 * All rest related functionalities of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2019, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns image mime types users are allowed to upload via the API.
 *
 * @return array
 */
function pno_rest_allowed_image_mime_types() {
	return apply_filters(
		'posterno_rest_allowed_image_mime_types',
		array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'bmp'          => 'image/bmp',
			'tiff|tif'     => 'image/tiff',
			'ico'          => 'image/x-icon',
		)
	);
}

/**
 * Upload image from URL.
 *
 * @param string $image_url Image URL.
 * @return array|WP_Error Attachment data or error message.
 */
function pno_rest_upload_image_from_url( $image_url ) {
	$parsed_url = wp_parse_url( $image_url );

	// Check parsed URL.
	if ( ! $parsed_url || ! is_array( $parsed_url ) ) {
		/* translators: %s: image URL */
		return new WP_Error( 'posterno_rest_invalid_image_url', sprintf( __( 'Invalid URL %s.', 'posterno' ), $image_url ), array( 'status' => 400 ) );
	}

	// Ensure url is valid.
	$image_url = esc_url_raw( $image_url );

	// download_url function is part of wp-admin.
	if ( ! function_exists( 'download_url' ) ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$file_array         = array();
	$file_array['name'] = basename( current( explode( '?', $image_url ) ) );

	// Download file to temp location.
	$file_array['tmp_name'] = download_url( $image_url );

	// If error storing temporarily, return the error.
	if ( is_wp_error( $file_array['tmp_name'] ) ) {
		return new WP_Error(
			'posterno_rest_invalid_remote_image_url',
			/* translators: %s: image URL */
			sprintf( __( 'Error getting remote image %s.', 'posterno' ), $image_url ) . ' '
			/* translators: %s: error message */
			. sprintf( __( 'Error: %s', 'posterno' ), $file_array['tmp_name']->get_error_message() ),
			array( 'status' => 400 )
		);
	}

	// Do the validation and storage stuff.
	$file = wp_handle_sideload(
		$file_array,
		array(
			'test_form' => false,
			'mimes'     => pno_rest_allowed_image_mime_types(),
		),
		current_time( 'Y/m' )
	);

	if ( isset( $file['error'] ) ) {
		@unlink( $file_array['tmp_name'] ); // @codingStandardsIgnoreLine.

		/* translators: %s: error message */
		return new WP_Error( 'posterno_rest_invalid_image', sprintf( __( 'Invalid image: %s', 'posterno' ), $file['error'] ), array( 'status' => 400 ) );
	}

	do_action( 'posterno_rest_api_uploaded_image_from_url', $file, $image_url );

	return $file;
}

/**
 * Set uploaded image as attachment.
 *
 * @param array $upload Upload information from wp_upload_bits.
 * @param int   $id Post ID. Default to 0.
 * @return int Attachment ID
 */
function pno_rest_set_uploaded_image_as_attachment( $upload, $id = 0 ) {
	$info    = wp_check_filetype( $upload['file'] );
	$title   = '';
	$content = '';

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		include_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$image_meta = wp_read_image_metadata( $upload['file'] );
	if ( $image_meta ) {
		if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
			$title = pno_clean( $image_meta['title'] );
		}
		if ( trim( $image_meta['caption'] ) ) {
			$content = pno_clean( $image_meta['caption'] );
		}
	}

	$attachment = array(
		'post_mime_type' => $info['type'],
		'guid'           => $upload['url'],
		'post_parent'    => $id,
		'post_title'     => $title ? $title : basename( $upload['file'] ),
		'post_content'   => $content,
	);

	$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $id );
	if ( ! is_wp_error( $attachment_id ) ) {
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	}

	return $attachment_id;
}
