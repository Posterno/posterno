<?php
/**
 * Registers all the actions for the administration.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Delete cached list of pages when a page is updated or created.
 * This is needed to refresh the list of available pages for the options panel.
 *
 * @param string $post_id
 * @return void
 */
function pno_delete_pages_transient( $post_id ) {
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	delete_transient( 'pno_get_pages' );
}
add_action( 'save_post_page', 'pno_delete_pages_transient' );
