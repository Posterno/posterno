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
		add_action( 'wp_ajax_pno_get_tags_from_categories_for_submission', [ $this, 'get_tags_from_categories_for_submission' ] );
		add_action( 'wp_ajax_nopriv_pno_get_tags_from_categories_for_submission', [ $this, 'get_tags_from_categories_for_submission' ] );

		add_action( 'wp_ajax_pno_get_tags', [ $this, 'get_tags' ] );
		add_action( 'wp_ajax_nopriv_pno_get_tags', [ $this, 'get_tags' ] );
		add_action( 'wp_ajax_pno_get_subcategories', [ $this, 'get_subcategories' ] );
		add_action( 'wp_ajax_nopriv_pno_get_subcategories', [ $this, 'get_subcategories' ] );
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
	public function get_tags_from_categories_for_submission() {

		check_ajax_referer( 'pno_get_tags_from_categories_for_submission', 'nonce' );

		$tags_ids   = [];
		$categories = isset( $_GET['categories'] ) ? (array) $_GET['categories'] : array();
		$categories = array_map( 'esc_attr', $categories );

		$subcategories_enabled = pno_get_option( 'submission_categories_sublevel' );

		$top_parent_categories = [];

		if ( ! empty( $categories ) && is_array( $categories ) ) {
			foreach ( $categories as $submitted_category_id ) {
				$parent_term = pno_get_term_top_most_parent( $submitted_category_id, 'listings-categories' );
				if ( $parent_term instanceof WP_Term ) {
					$top_parent_categories[] = absint( $parent_term->term_id );
				}
			}
		}

		$categories = array_unique( $top_parent_categories );

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
	 * Retrieve sub categories from the database.
	 *
	 * @return void
	 */
	public function get_subcategories() {

		check_ajax_referer( 'pno_get_subcategories', 'nonce' );

		$parent_categories = isset( $_GET['categories'] ) && ! empty( $_GET['categories'] ) && is_array( $_GET['categories'] ) ? array_map( 'absint', $_GET['categories'] ) : false;
		$sub_categories    = [];

		foreach ( $parent_categories as $parent_category_id ) {

			$childs = get_term_children( $parent_category_id, 'listings-categories' );

			$terms_args = [
				'hide_empty' => false,
				'number'     => 999,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'include'    => $childs,
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
