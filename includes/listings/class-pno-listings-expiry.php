<?php
/**
 * Handles expiration of listings.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles registration of expiration fields and actions that expire listings.
 */
class PNO_Listings_Expiry {

	/**
	 * Get things started.
	 *
	 * @return void
	 */
	public function init() {

		// Stop everything if expiration is disabled.
		if ( ! pno_listings_can_expire() ) {
			add_filter( 'pno_listings_table_columns', [ $this, 'disable_dashboard_expiry_column' ] );
			return;
		}

		add_action( 'carbon_fields_register_fields', [ $this, 'register_settings' ] );

		add_action( 'pending_to_publish', [ $this, 'set_expiry' ] );
		add_action( 'preview_to_publish', [ $this, 'set_expiry' ] );
		add_action( 'draft_to_publish', [ $this, 'set_expiry' ] );
		add_action( 'auto-draft_to_publish', [ $this, 'set_expiry' ] );
		add_action( 'expired_to_publish', [ $this, 'set_expiry' ] );

	}

	/**
	 * Disable expiry column within the dashboard table.
	 *
	 * @param array $cols registered columns.
	 * @return array
	 */
	public function disable_dashboard_expiry_column( $cols ) {
		if ( isset( $cols['expires'] ) ) {
			unset( $cols['expires'] );
		}
		return $cols;
	}

	/**
	 * Add a new metabox within the listings post type that holds the
	 * listings expiration date picker.
	 *
	 * @return void
	 */
	public function register_settings() {

		$format = get_option( 'date_format' );

		Container::make( 'post_meta', esc_html__( 'Expiry' ) )
			->where( 'post_type', '=', 'listings' )
			->set_context( 'side' )
			->set_priority( 'low' )
			->add_fields(
				array(
					Field::make( 'date', 'listing_expires', esc_html__( 'Listing expiry date' ) )
						->set_input_format( $format, $format )
						->set_storage_format( 'Y-m-d' )
						->set_attribute( 'placeholder', pno_calculate_listing_expiry() ),
				)
			);

	}

	/**
	 * Automatically set expiry date when creating or publishing listings.
	 *
	 * @param object $post the post object.
	 * @return void
	 */
	public function set_expiry( $post ) {

		if ( 'listings' !== $post->post_type ) {
			return;
		}

		// See if it is already set.
		if ( metadata_exists( 'post', $post->ID, '_listing_expires' ) ) {
			$expires = get_post_meta( $post->ID, '_listing_expires', true );
			if ( $expires && strtotime( $expires ) < current_time( 'timestamp' ) ) {
				update_post_meta( $post->ID, '_listing_expires', '' );
			}
		}

		// See if the user has set the expiry manually.
		if ( ! empty( $_POST['_listing_expires'] ) ) {

			update_post_meta( $post->ID, '_listing_expires', date( 'Y-m-d', strtotime( sanitize_text_field( $_POST['_listing_expires'] ) ) ) );

		} elseif ( ! isset( $expires ) ) {

			// No manual setting? Lets generate a date if there isn't already one.
			$expires = pno_calculate_listing_expiry( $post->ID );

			update_post_meta( $post->ID, '_listing_expires', $expires );

			// In case we are saving a post, ensure post data is updated so the field is not overridden.
			if ( isset( $_POST['_listing_expires'] ) ) {
				$_POST['_listing_expires'] = $expires;
			}
		}

	}

}

( new PNO_Listings_Expiry() )->init();
