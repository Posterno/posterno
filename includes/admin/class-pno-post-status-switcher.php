<?php
/**
 * Handles the custom listings status switcher in the admin panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

use PNO\Form\Form;
use PNO\Form\DefaultSanitizer;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles the custom listings status switcher in the admin panel.
 */
class PostStatusSwitcher {

	use DefaultSanitizer;

	/**
	 * List of statuses registered for listings.
	 *
	 * @var array
	 */
	public $statuses = [];

	/**
	 * Form object.
	 *
	 * @var Form
	 */
	public $form = null;

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->statuses = pno_get_listing_post_statuses();
	}

	/**
	 * Hook into WP.
	 *
	 * @return void
	 */
	public function init() {

		add_action( 'post_submitbox_misc_actions', [ $this, 'display' ] );
		add_action( 'save_post', [ $this, 'save_status' ] );

	}

	/**
	 * Display the post status switcher in the publish box.
	 *
	 * @param object $listing post object.
	 * @return void
	 */
	public function display( $listing ) {

		$this->form = Form::createFromConfig( $this->get_fields( $listing ) );
		$this->addSanitizer( $this->form );

		$post_type = get_post_type( $listing );

		if ( $post_type !== 'listings' || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		include PNO_PLUGIN_DIR . 'includes/admin/views/status-switcher.php';

	}

	/**
	 * Get list of fields for the form.
	 *
	 * @return array
	 */
	public function get_fields( $listing ) {

		$fields = [
			'listing_status' => [
				'type'       => 'select',
				'label'      => esc_html__( 'Status', 'posterno' ),
				'values'     => $this->statuses,
				'value'      => esc_attr( $listing->post_status ),
				'attributes' => [
					'class' => 'form-control',
				],
			],
		];

		return $fields;

	}

	/**
	 * Save the status of the listing.
	 *
	 * @param string $post_id the id of the listing.
	 * @return void
	 */
	public function save_status( $post_id ) {

		check_admin_referer( 'pno_change_listing_status', 'pno_set_listing_status' );

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( get_post_type( $post_id ) !== 'listings' || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$current_status       = get_post_status( $post_id );
		$submitted_status     = isset( $_POST['listing_status'] ) && ! empty( $_POST['listing_status'] ) ? pno_clean( $_POST['listing_status'] ) : false;
		$is_wp_status_publish = isset( $_POST['post_status'] ) && $_POST['post_status'] === 'publish' ? true : false;

		//if ( $is_wp_status_publish && $submitted_status !== 'publish' ) {
		//	return;
		//}

		if ( $submitted_status === $current_status ) {
			return;
		}

		if ( ! array_key_exists( $submitted_status, $this->statuses ) ) {
			return;
		}

		wp_update_post(
			[
				'ID'          => $post_id,
				'post_status' => $submitted_status,
			]
		);

	}

}

( new PostStatusSwitcher() )->init();
