<?php
/**
 * List of filters related to the profiles functionality.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Replaces the public author url with the member's profile url of Posterno.
 * Only when the option is enabled.
 *
 * @param string $link the current link.
 * @param int    $author_id       Author ID.
 * @param string $author_nicename Optional. The author's nicename (slug). Default empty.
 * @return string The URL to the author's page.
 */
function pno_replace_author_link( $link, $author_id, $author_nicename ) {
	return pno_get_member_profile_url( $author_id );
}
if ( pno_get_option( 'profiles_replace_author', false ) ) {
	add_filter( 'author_link', 'pno_replace_author_link', 10, 3 );
}
