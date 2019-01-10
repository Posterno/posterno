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

			echo '<strong>';
			pno_the_listing_expire_date( $listing_id );
			echo '</strong>';

		} elseif ( $column === 'posted' ) {

			echo '<strong>';
			pno_the_listing_publish_date( $listing_id );
			echo '</strong><br/>';

			$post_author_id = get_post_field( 'post_author', $listing_id );
			$author_name    = get_the_author_meta( 'display_name', $post_author_id );
			$admin_url      = add_query_arg(
				[
					'user_id' => $post_author_id,
				],
				admin_url( 'user-edit.php' )
			);

			echo wp_sprintf( __( 'By: %1$s' ), '<strong><a href="' . esc_url( $admin_url ) . '">' . esc_html( $author_name ) . '</a></strong>' );

		} elseif ( $column === 'type' ) {
			echo 'd';
		} elseif ( $column === 'categories' ) {
			echo 'dd';
		}

	}

}

( new ListingsTable() )->init();
