<?php
/**
 * List of actions triggered on profile pages.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Displays the content of the "about" component on profile pages.
 *
 * @param string|int $user_id the queried user's id.
 * @param WP_User    $user_details some details about the user.
 * @return void
 */
function pno_profile_about_page( $user_id, $user_details ) {

}
add_action( 'pno_profile_content_slot_about', 'pno_profile_about_page', 10, 2 );

/**
 * Displays the content of the "posts" component on profile pages.
 *
 * @param string|int $user_id the queried user's id.
 * @param WP_User    $user_details some details about the user.
 * @return void
 */
function pno_profile_posts_page( $user_id, $user_details ) {

	if ( ! $user_id ) {
		return;
	}

	ob_start();

	posterno()->templates
		->set_template_data(
			[
				'user_id' => $user_id,
				'user_details' => $user_details,
			]
		)
		->get_template_part( 'profile/posts' );

	$output = ob_get_clean();

	echo $output;

}
add_action( 'pno_profile_content_slot_posts', 'pno_profile_posts_page', 10, 2 );
