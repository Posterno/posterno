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
	public static function init() {
		add_action( 'wp_ajax_pno_get_tags_from_categories', [ __CLASS__, 'get_tags_from_categories' ] );
		add_action( 'wp_ajax_nopriv_pno_get_tags_from_categories', [ __CLASS__, 'get_tags_from_categories' ] );
	}

	/**
	 * Uploads file from an Ajax request.
	 *
	 * No nonce field since the form may be statically cached.
	 */
	public static function upload_file() {
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

		$categories = isset( $_GET['categories'] ) ? (array) $_GET['categories'] : array();
		$categories = array_map( 'esc_attr', $categories );

		if ( ! empty( $categories ) ) {

			$terms_args = [
				'hide_empty' => false,
				'number'     => 999,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'meta_query' => [
					[
						'key'     => '_associated_categories_for_tags',
						'value'   => $categories,
						'compare' => 'IN',
					],
				],
			];

			$tags = get_terms( 'listings-tags', $terms_args );

			wp_send_json_success( $tags );

		} else {
			wp_send_json_error( null, 422 );
		}

	}

}

( new PNO_Ajax() )->init();
