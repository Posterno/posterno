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

/**
 * Replace the comment author's URL with Posterno's profile url.
 *
 * @param string     $url     The comment author's URL.
 * @param int        $id      The comment ID.
 * @param WP_Comment $comment The comment object.
 */
function pno_replace_comment_author_url( $url, $id, $comment ) {

	if ( ! empty( $comment ) && isset( $comment->user_id ) && $comment->user_id > 0 ) {
		$url = pno_get_member_profile_url( $comment->user_id );
	}

	return $url;

}
if ( pno_get_option( 'profiles_replace_comments_author', false ) ) {
	add_filter( 'get_comment_author_url', 'pno_replace_comment_author_url', 10, 3 );
}
