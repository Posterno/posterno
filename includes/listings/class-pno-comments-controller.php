<?php
/**
 * Close comments for listings when the option is enabled.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Controls whether or not comments should be disable for listings.
 */
class PNO_Comments_Controller {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {

		if ( ! pno_get_option( 'listings_disable_comments' ) ) {
			return;
		}

		add_filter( 'comments_array', [ $this, 'filter_existing_comments' ], 20, 2 );
		add_filter( 'comments_open', [ $this, 'filter_comment_status' ], 20, 2 );
		add_filter( 'pings_open', [ $this, 'filter_comment_status' ], 20, 2 );

		add_action( 'admin_init', [ $this, 'remove_comments' ] );

	}

	/**
	 * Remove comments from the admin dashboard for listings post type.
	 *
	 * @return void
	 */
	public function remove_comments() {

		$post_type = 'listings';

		remove_meta_box( 'commentstatusdiv', $post_type, 'normal' );
		remove_meta_box( 'trackbacksdiv', $post_type, 'normal' );

		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}

	}

	/**
	 * Disable comments.
	 *
	 * @param array  $comments list of comments submitted.
	 * @param string $post_id the post id.
	 * @return array|boolean
	 */
	public function filter_existing_comments( $comments, $post_id ) {

		$post = get_post( $post_id );

		if ( $post->post_type === 'listings' ) {
			return false;
		}

		return $comments;

	}

	/**
	 * Disable comments.
	 *
	 * @param boolean $open open or not.
	 * @param string  $post_id the post id.
	 * @return boolean
	 */
	public function filter_comment_status( $open, $post_id ) {
		$post = get_post( $post_id );

		if ( $post->post_type === 'listings' ) {
			return false;
		}

		return $open;

	}

}

( new PNO_Comments_Controller() )->init();
