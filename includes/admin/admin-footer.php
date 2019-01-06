<?php
/**
 * Admin footer.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add rating links to the admin dashboard.
 *
 * @since       0.1.0
 * @global      string $typenow
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function pno_admin_rate_us( $footer_text ) {

	global $typenow;

	$screen = get_current_screen();

	$checks = [
		'edit-listings',
		'listings',
		'edit-listings-types',
		'edit-listings-categories',
		'edit-listings-locations',
		'edit-listings-tags',
		'listings_page_posterno-custom-listings-fields',
		'pno_listings_fields',
		'users_page_posterno-custom-profile-fields',
		'pno_users_fields',
		'users_page_posterno-custom-registration-form',
		'pno_signup_fields',
		'edit-pno_emails',
		'pno_emails',
		'settings_page_posterno-options',
		'admin_page_posterno-options[accounts]',
		'admin_page_posterno-options[emails]',
		'admin_page_posterno-options[listings]'
	];

	if ( in_array( $screen->id, $checks ) ) {
		$rate_text = sprintf(
			__( 'Thank you for using <a href="%1$s" target="_blank">Posterno</a>! Please <a href="%2$s" target="_blank">rate us on WordPress.org</a>' ),
			'https://posterno.com',
			'https://wordpress.org/support/plugin/posterno/reviews/?rate=5#new-post'
		);
		return str_replace( '</span>', '', $footer_text ) . ' | ' . $rate_text . '</span> <span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span><span class="dashicons dashicons-star-filled footer-star"></span></span>';
	} else {
		return $footer_text;
	}
}
add_filter( 'admin_footer_text', 'pno_admin_rate_us' );
