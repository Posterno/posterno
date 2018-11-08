<?php
/**
 * Handles ajax integration with WordPress.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Integration with WordPress Ajax.
 */
class PNO_Ajax {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_ajax_pno_get_tags_from_categories', [ $this, 'get_tags_from_categories' ] );
		add_action( 'wp_ajax_nopriv_pno_get_tags_from_categories', [ $this, 'get_tags_from_categories' ] );
		add_action( 'wp_ajax_pno_get_tags', [ $this, 'get_tags' ] );
		add_action( 'wp_ajax_nopriv_pno_get_tags', [ $this, 'get_tags' ] );
		add_action( 'wp_ajax_pno_get_subcategories', [ $this, 'get_subcategories' ] );
		add_action( 'wp_ajax_nopriv_pno_get_subcategories', [ $this, 'get_subcategories' ] );

		add_action( 'wp_ajax_pno_dropzone_upload', [ $this, 'dropzone_upload' ] );
		add_action( 'wp_ajax_nopriv_pno_dropzone_upload', [ $this, 'dropzone_upload' ] );

		add_action( 'wp_ajax_pno_remove_dropzone_file', [ $this, 'dropzone_delete' ] );
		add_action( 'wp_ajax_nopriv_pno_remove_dropzone_file', [ $this, 'dropzone_delete' ] );

		add_action( 'wp_ajax_pno_unattach_files_from_listing', [ $this, 'unattach_from_listing' ] );
		add_action( 'wp_ajax_nopriv_pno_unattach_files_from_listing', [ $this, 'unattach_from_listing' ] );
	}

	/**
	 * Uploads file from an Ajax request.
	 *
	 * No nonce field since the form may be statically cached.
	 */
	public function upload_file() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'You must be logged in to upload files using this method.' ) );
			return;
		}
		$data = array(
			'files' => array(),
		);
		if ( ! empty( $_FILES ) ) {
			foreach ( $_FILES as $file_key => $file ) {
				$files_to_upload = pno_prepare_uploaded_files( $file );
				foreach ( $files_to_upload as $file_to_upload ) {
					$uploaded_file = pno_upload_file(
						$file_to_upload,
						array(
							'file_key' => $file_key,
						)
					);
					if ( is_wp_error( $uploaded_file ) ) {
						$data['files'][] = array(
							'error' => $uploaded_file->get_error_message(),
						);
					} else {
						$data['files'][] = $uploaded_file;
					}
				}
			}
		}
		wp_send_json( $data );
	}

	/**
	 * Retrieve a list of tags given categories ids.
	 *
	 * @return void
	 */
	public static function get_tags_from_categories() {

		check_ajax_referer( 'pno_get_tags_from_categories', 'nonce' );

		$tags_ids   = [];
		$categories = isset( $_GET['categories'] ) ? (array) $_GET['categories'] : array();
		$categories = array_map( 'esc_attr', $categories );

		$subcategories_enabled = pno_get_option( 'submission_categories_sublevel' );

		// With subcategories enabled, go and find the top level category where tags have been associated.
		if ( $subcategories_enabled ) {
			$parent_categories = [];
			foreach ( $categories as $category_id ) {
				$found_parent_term = pno_get_term_top_most_parent( $category_id, 'listings-categories' );
				if ( isset( $found_parent_term->term_id ) ) {
					$parent_categories[] = absint( $found_parent_term->term_id );
				}
			}
			$categories = array_unique( $parent_categories );
		}

		// Retrieve the tags associated to the found top level categories.
		if ( ! empty( $categories ) && is_array( $categories ) ) {
			foreach ( $categories as $parent_category_id ) {
				$associated_tags = carbon_get_term_meta( $parent_category_id, 'associated_tags' );
				if ( $associated_tags ) {
					$tags_ids = array_merge( $tags_ids, $associated_tags );
				}
			}
		}

		if ( ! empty( $tags_ids ) ) {

			$terms_args = [
				'hide_empty' => false,
				'number'     => 999,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'include'    => array_unique( $tags_ids ),
			];

			$tags = get_terms( 'listings-tags', $terms_args );

			wp_send_json_success( $tags );

		} else {
			wp_send_json_error( null, 422 );
		}

	}

	/**
	 * Retrieve tags from the database.
	 *
	 * @return void
	 */
	public function get_tags() {

		check_ajax_referer( 'pno_get_tags', 'nonce' );

		$terms_args = [
			'hide_empty' => false,
			'number'     => 50,
			'orderby'    => 'name',
			'order'      => 'ASC',
		];

		$tags = get_terms( 'listings-tags', $terms_args );

		wp_send_json_success( $tags );

	}

	/**
	 * Upload files via dropzone fields.
	 *
	 * @return void
	 */
	public function dropzone_upload() {

		if ( ! isset( $_GET['dropzone_id'] ) ) { //phpcs:ignore
			wp_send_json_error( false, 422 );
		}

		$dropzone_id = sanitize_text_field( $_GET['dropzone_id'] );
		$multiple    = isset( $_POST['multiple'] ) && (bool) $_POST['multiple'] ? true : false;
		$field_id    = isset( $_POST['field_id'] ) && ! empty( $_POST['field_id'] ) ? sanitize_text_field( $_POST['field_id'] ) : false;

		if ( ! $field_id ) {
			wp_send_json_error( false, 422 );
		}

		check_ajax_referer( 'pno_dropzone_upload', $dropzone_id );

		$data = array(
			'files' => array(),
		);

		if ( ! empty( $_FILES ) ) {
			foreach ( $_FILES as $file_key => $file ) {
				$file_key        = $field_id;
				$files_to_upload = pno_prepare_uploaded_files( $file );
				foreach ( $files_to_upload as $file_to_upload ) {
					$uploaded_file = pno_upload_file(
						$file_to_upload,
						array(
							'file_key' => $file_key,
						)
					);
					if ( is_wp_error( $uploaded_file ) ) {
						wp_send_json_error( [ 'message' => $uploaded_file->get_error_message() ], 422 );
					} else {
						$data['files'][] = $uploaded_file;
					}
				}
			}
		}

		if ( empty( $data['files'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Something went wrong during upload' ) ], 422 );
		}

		wp_send_json_success( $data );

	}

	/**
	 * Remove files from a dropzone.
	 *
	 * @return void
	 */
	public function dropzone_delete() {

		check_ajax_referer( 'pno_dropzone_remove_file_nonce', 'nonce' );

		if ( ! isset( $_POST['file_path'] ) || ! isset( $_POST['file_url'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Something went wrong while removing the file.' ) ], 422 );
		}

		$file_path = sanitize_text_field( $_POST['file_path'] );

		wp_delete_file( $file_path );

		wp_send_json_success();

	}

	/**
	 * Delete attachments from listings.
	 *
	 * @return void
	 */
	public function unattach_from_listing() {

		check_ajax_referer( 'pno_dropzone_unattach_from_listing', 'nonce' );

		if ( ! isset( $_POST['attachment_id'] ) || ! isset( $_POST['listing_id'] ) ) {
			return;
		}

		$user_id       = get_current_user_id();
		$listing_id    = absint( $_POST['listing_id'] );
		$attachment_id = absint( $_POST['attachment_id'] );

		if ( pno_is_user_owner_of_listing( $user_id, $listing_id ) && $attachment_id ) {

			wp_delete_attachment( $attachment_id, true );

			// Now detect if the attachment is also part of the media's list
			// attached to the listing and delete it.
			$attached_medias  = get_post_meta( $listing_id, '_listing_gallery_images', true );
			$attachments_list = [];

			if ( is_array( $attached_medias ) && ! empty( $attached_medias ) ) {
				foreach ( $attached_medias as $media ) {
					if ( isset( $media['value'] ) ) {
						$attachments_list[] = $media['value'];
					}
				}

				if ( ! empty( $attachments_list ) && in_array( $attachment_id, $attachments_list ) ) {
					if ( ( $key = array_search( $attachment_id, $attachments_list ) ) !== false ) {
						unset( $attachments_list[ $key ] );
					}
				}

				// Reformat the attachments list array to be compatible with the database storage.
				$new_storage_array = [];
				foreach ( $attachments_list as $media_id ) {
					$new_storage_array[] = [ 'value' => absint( $media_id ) ];
				}

				update_post_meta( $listing_id, '_listing_gallery_images', $new_storage_array );
			}

			wp_send_json_success();

		} else {
			wp_send_json_error( [ 'message' => esc_html__( 'Something went wrong while removing files.' ) ], 422 );
		}

	}

	/**
	 * Retrieve sub categories from the database.
	 *
	 * @return void
	 */
	public function get_subcategories() {

		check_ajax_referer( 'pno_get_subcategories', 'nonce' );

		$parent_categories = isset( $_GET['categories'] ) && ! empty( $_GET['categories'] ) && is_array( $_GET['categories'] ) ? array_map( 'absint', $_GET['categories'] ) : false;
		$sub_categories = [];

		foreach ( $parent_categories as $parent_category_id ) {

			$terms_args = [
				'hide_empty' => false,
				'number'     => 999,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'parent'     => $parent_category_id,
			];

			$found_sub_categories = get_terms( 'listings-categories', $terms_args );

			if ( ! empty( $found_sub_categories ) && is_array( $found_sub_categories ) ) {
				foreach ( $found_sub_categories as $sub_category ) {
					$sub_categories[] = $sub_category;
				}
			}

		}

		if ( ! empty( $sub_categories ) ) {
			wp_send_json_success( $sub_categories );
		} else {
			wp_send_json_error( false, 422 );
		}

	}

}

( new PNO_Ajax() )->init();
