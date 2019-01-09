<?php
/**
 * Handles definition of custom functionalities for the listings post type admin table.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles functionalities for the listings post type admin table.
 */
class ListingsTable {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {

		add_filter( 'manage_listings_posts_columns', [ $this, 'columns' ] );
		add_action( 'manage_listings_posts_custom_column', [ $this, 'columns_content' ], 10, 2 );

	}

	/**
	 * Adjusts columns for the listings post type admin table.
	 *
	 * @param array $columns already registered columns.
	 * @return array
	 */
	public function columns( $columns ) {

		if ( isset( $columns['author'] ) ) {
			unset( $columns['author'] );
		}

		unset( $columns['date'] );

		$columns['status']   = '<span class="dashicons dashicons-info"></span>';
		$columns['featured'] = '<span class="dashicons dashicons-star-filled"></span>';
		$columns['posted']   = esc_html__( 'Posted' );

		if ( pno_listings_can_expire() ) {
			$columns['expires'] = esc_html__( 'Expires' );
		}

		$columns['type']       = esc_html__( 'Type' );
		$columns['categories'] = esc_html__( 'Categories' );

		return $columns;

	}

	/**
	 * Define the content for the custom columns for the listings post type.
	 *
	 * @param string $column the name of the column.
	 * @param string $listing_id the post we're going to use.
	 * @return void
	 */
	public function columns_content( $column, $listing_id ) {

		if ( $column === 'expires' && pno_listings_can_expire() ) {
			pno_the_listing_expire_date( $listing_id );
		}

	}

}

( new ListingsTable() )->init();
