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

		$columns['status']   = '<div data-balloon="' . esc_html__( 'Status' ) . '" data-balloon-pos="right"><span class="dashicons dashicons-info"></span></div>';
		$columns['featured'] = '<div data-balloon="' . esc_html__( 'Featured' ) . '" data-balloon-pos="right"><span class="dashicons dashicons-star-filled"></span></div>';
		$columns['posted']   = esc_html__( 'Posted' );

		if ( pno_listings_can_expire() ) {
			$columns['expires'] = esc_html__( 'Expires' );
		}

		$columns['type'] = esc_html__( 'Type' );

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

			$type = pno_get_listing_type( $listing_id );

			if ( isset( $type->name ) ) {

				$url = add_query_arg(
					[
						'listings-types' => $type->slug,
						'post_type'      => 'listings',
					],
					admin_url( 'edit.php' )
				);

				echo '<a href="' . esc_url( $url ) . '">' . esc_html( $type->name ) . '</a>';
			} else {
				echo '–';
			}

		} elseif ( $column === 'featured' ) {

			if ( pno_listing_is_featured( $listing_id ) ) {
				echo '<span class="dashicons dashicons-star-filled"></span>';
			} else {
				echo '–';
			}

		} elseif ( $column === 'status' ) {

			$status = get_post_status( $listing_id );

			if ( $status === 'publish' ) {
				echo '<div data-balloon="' . esc_html__( 'Active' ) . '" data-balloon-pos="right"><span class="dashicons dashicons-yes"></span></div>';
			} elseif ( $status === 'expired' ) {
				echo '<div data-balloon="' . esc_html__( 'Expired' ) . '" data-balloon-pos="right"><span class="dashicons dashicons-dismiss"></span></div>';
			} elseif ( $status === 'pending' ) {
				echo '<div data-balloon="' . esc_html__( 'Pending approval' ) . '" data-balloon-pos="right"><span class="dashicons dashicons-clock"></span></div>';
			} else {
				echo '–';
			}

		}

	}

}

( new ListingsTable() )->init();
