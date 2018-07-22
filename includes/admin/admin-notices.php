<?php
/**
 * Admin notices.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Tell the user, the field can't be deleted.
 *
 * @return void
 */
function pno_is_default_field_notice() {

	$screen = get_current_screen();

	if ( $screen instanceof WP_Screen && $screen->id == 'pno_users_fields' ) {

		global $post;

		if ( $post instanceof WP_Post && isset( $post->ID ) && ! posterno()->admin_notices->is_dismissed( 'field_is_default_' . $post->ID ) ) {

			$is_default_field = get_post_meta( $post->ID, 'is_default_field', true );

			if ( $is_default_field ) {

				$message = esc_html__( 'This is a default field. Default fields cannot have their type and meta key changed and can\'t be deleted.' );

				posterno()->admin_notices->register_notice( 'field_is_default_' . $post->ID, 'info', $message );

			}
		}
	}

}
add_action( 'admin_head', 'pno_is_default_field_notice' );
