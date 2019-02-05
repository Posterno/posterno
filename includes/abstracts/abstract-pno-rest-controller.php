<?php
/**
 * Abstract with common methods that handle extension of the WordPress rest api.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Posterno's rest controller class.
 */
abstract class PNO_REST_Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'posterno/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Check if a given request can read resources.
	 *
	 * @return mixed
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'posterno_rest_cannot_view', esc_html__( 'Sorry, you cannot list resources.', 'posterno' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check if a given request can create profile fields.
	 *
	 * @return mixed
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'posterno_rest_cannot_create', esc_html__( 'Sorry, you are not allowed to create resources.', 'posterno' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check if a given request can update resources.
	 *
	 * @return mixed
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'posterno_rest_cannot_update', esc_html__( 'Sorry, you cannot update resources.', 'posterno' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check if a given request can delete resources.
	 *
	 * @return mixed
	 */
	public function delete_item_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'posterno_rest_cannot_delete', esc_html__( 'Sorry, you cannot delete resources.', 'posterno' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check if a given request can work with batch resources.
	 *
	 * @return mixed
	 */
	public function batch_items_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'posterno_rest_cannot_batch', esc_html__( 'Sorry, you cannot work with batch resources.', 'posterno' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Retrieves the query params for the models collection.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		return $query_params;

	}

}
