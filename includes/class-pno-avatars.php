<?php
/**
 * Handles avatar functionalities.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Avatar handler class.
 */
class PNO_Avatars {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public static function init() {

		if ( ! pno_get_option( 'allow_avatars' ) ) {
			return;
		}

		add_action( 'carbon_fields_register_fields', [ __class__, 'avatar_field' ] );
		add_filter( 'get_avatar_url', [ __class__, 'set_avatar_url' ], 10, 3 );
	}

	/**
	 * Retrieve the correct user ID based on whichever page we're viewing.
	 *
	 * @param mixed $id_or_email identifier for the user.
	 * @return mixed
	 */
	private static function get_user_id( $id_or_email ) {

		$retval = 0;

		if ( is_numeric( $id_or_email ) ) {

			$retval = $id_or_email;

		} elseif ( is_string( $id_or_email ) ) {

			$user_by = is_email( $id_or_email ) ? 'email' : 'login';

			$user = get_user_by( $user_by, $id_or_email );

			if ( ! empty( $user ) ) {
				$retval = $user->ID;
			}
		} elseif ( $id_or_email instanceof WP_User ) {
			$user = $id_or_email->ID;
		} elseif ( $id_or_email instanceof WP_Post ) {
			$retval = $id_or_email->post_author;
		} elseif ( $id_or_email instanceof WP_Comment ) {
			if ( ! empty( $id_or_email->user_id ) ) {
				$retval = $id_or_email->user_id;
			}
		}

		return (int) apply_filters( 'pno_avatars_get_user_id', (int) $retval, $id_or_email );

	}

	/**
	 * Add avatar field in the WordPress backend.
	 *
	 * @return void
	 */
	public static function avatar_field() {
		Container::make( 'user_meta', esc_html__( 'Profile picture & cover' ) )
			->add_fields(
				array(
					Field::make( 'image', 'current_user_avatar', esc_html__( 'Custom user avatar' ) )
						->set_value_type( 'url' ),
					Field::make( 'image', 'user_cover', esc_html__( 'Custom profile cover image' ) )
						->set_value_type( 'url' ),
				)
			);
	}

	/**
	 * Override WordPress default avatar URL with the custom one.
	 *
	 * @param string $url url of the avatar.
	 * @param mixed  $id_or_email id or email of the user.
	 * @param array  $args additional args.
	 * @return mixed
	 */
	public static function set_avatar_url( $url, $id_or_email, $args ) {

		// Bail if forcing default.
		if ( ! empty( $args['force_default'] ) ) {
			return $url;
		}

		// Bail if explicitly an md5'd Gravatar url.
		if ( is_string( $id_or_email ) && strpos( $id_or_email, '@md5.gravatar.com' ) ) {
			return $url;
		}

		$custom_avatar = carbon_get_user_meta( self::get_user_id( $id_or_email ), 'current_user_avatar' );

		if ( $custom_avatar && $custom_avatar !== 'false' ) {
			$url = $custom_avatar;
		}

		return apply_filters( 'pno_get_avatar_url', $url, $id_or_email );

	}

}

( new PNO_Avatars() )->init();
