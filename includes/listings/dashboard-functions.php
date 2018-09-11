<?php
/**
 * List of functions used within the frontend dashboard.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the list of columns to the listings management dashboard.
 *
 * @return array
 */
function pno_get_listings_table_columns() {

	$columns = [
		'name'    => esc_html__( 'Listing name' ),
		'date'    => esc_html__( 'Date posted' ),
		'expires' => esc_html__( 'Expires' ),
		'actions' => esc_html__( 'Actions' ),
	];

	/**
	 * Allow developers to customize the columns within the listings management dashboard.
	 *
	 * @param array $columns
	 */
	return apply_filters( 'pno_listings_table_columns', $columns );

}
