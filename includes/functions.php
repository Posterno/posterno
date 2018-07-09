<?php
/**
 * List of functions used all around within the plugin.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the ID number of the selected login page.
 *
 * @return mixed
 */
function pno_get_login_page_id() {

	$login_page  = false;
	$page_option = pno_get_option( 'login_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$login_page = absint( $page_option['value'] );
	}

	return $login_page;

}
