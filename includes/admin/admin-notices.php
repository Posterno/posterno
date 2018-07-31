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
	} elseif ( $screen instanceof WP_Screen && $screen->id == 'pno_signup_fields' ) {
		global $post;

		if ( $post instanceof WP_Post && isset( $post->ID ) && ! posterno()->admin_notices->is_dismissed( 'field_is_default_' . $post->ID ) ) {

			$is_default_field = carbon_get_post_meta( $post->ID, 'field_is_default' );

			if ( $is_default_field ) {

				$message = esc_html__( 'This is a default field. Default fields cannot be deleted.' );

				posterno()->admin_notices->register_notice( 'field_is_default_' . $post->ID, 'info', $message );

			}
		}
	}

}
add_action( 'admin_head', 'pno_is_default_field_notice' );

/**
 * Display a notice when the avatar field is disabled and the user is editing the field.
 *
 * @return void
 */
function pno_avatar_field_is_disabled_notice() {

	$screen = get_current_screen();

	if ( $screen instanceof WP_Screen && $screen->id == 'pno_users_fields' ) {

		global $post;

		if ( $post instanceof WP_Post && isset( $post->ID ) && get_post_meta( $post->ID, '_field_meta_key', true ) == 'avatar' && ! pno_get_option( 'allow_avatars' ) ) {

			$message = esc_html__( 'The avatar field is currently disabled. If needed, you can enable it through the plugin\'s settings.' );

			posterno()->admin_notices->register_notice( 'avatar_disabled', 'info', $message, [ 'dismissible' => false ] );

		}
	}

}
add_action( 'admin_head', 'pno_avatar_field_is_disabled_notice' );
