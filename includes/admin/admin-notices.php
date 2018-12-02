<?php
/**
 * Admin notices.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display a notice when the avatar field is disabled and the user is editing the field.
 *
 * @return void
 */
function pno_avatar_field_is_disabled_notice() {

	$screen = get_current_screen();

	if ( $screen instanceof WP_Screen && $screen->id == 'pno_users_fields' ) {

		global $post;

		$field = new PNO\Field\Profile( $post->ID );

		if ( $post instanceof WP_Post && isset( $post->ID ) && $field->get_object_meta_key() === 'avatar' && ! pno_get_option( 'allow_avatars' ) ) {

			$message = esc_html__( 'The avatar field is currently disabled. You can enable it through the plugin\'s settings.' );

			posterno()->admin_notices->register_notice( 'avatar_disabled', 'info', $message, [ 'dismissible' => false ] );

		}
	}

}
add_action( 'admin_head', 'pno_avatar_field_is_disabled_notice' );

/**
 * Display an error notice if the user has wrongly configured the password settings.
 *
 * @return void
 */
function pno_password_settings_controller_notice() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-settings' ) {
		return;
	}

	if ( pno_get_option( 'disable_password' ) && pno_get_option( 'verify_password' ) ) {

		$message = esc_html__( 'Posterno: you have enabled the "Disable custom passwords during registration" setting, please disable the "Enable password confirmation" option.' );

		posterno()->admin_notices->register_notice( 'psw_setting_error', 'error', $message, [ 'dismissible' => false ] );

	}

}
add_action( 'admin_head', 'pno_password_settings_controller_notice' );

/**
 * Display a notice if avatars are currently globally disabled onto the site.
 *
 * @return void
 */
function pno_avatar_globally_disabled_notice() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-settings' ) {
		return;
	}

	if ( pno_get_option( 'allow_avatars' ) && ! get_option( 'show_avatars' ) ) {

		$admin_url     = admin_url( 'options-discussion.php#show_avatars' );
		$pno_admin_url = admin_url( 'edit.php?post_type=listings&page=posterno-settings' );

		$message = sprintf( __( 'Posterno: avatars <a href="%1$s">are currently globally disabled</a> onto your site, please <a href="%1$s">enable avatars</a> onto your site or <a href="%2$s">disable Posterno\'s built-in custom avatars</a>.' ), $admin_url, $pno_admin_url );

		posterno()->admin_notices->register_notice( 'avatar_setting_error', 'error', $message, [ 'dismissible' => false ] );

	}

}
add_action( 'admin_head', 'pno_avatar_globally_disabled_notice' );
