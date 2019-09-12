<?php
/**
 * List of functions used within the frontend dashboard.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
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
		'name'    => esc_html__( 'Listing name', 'posterno' ),
		'date'    => esc_html__( 'Date posted', 'posterno' ),
		'expires' => esc_html__( 'Expires', 'posterno' ),
		'status'  => esc_html__( 'Status', 'posterno' ),
		'actions' => esc_html__( 'Actions', 'posterno' ),
	];

	/**
	 * Allow developers to customize the columns within the listings management dashboard.
	 *
	 * @param array $columns
	 */
	return apply_filters( 'pno_listings_table_columns', $columns );

}
