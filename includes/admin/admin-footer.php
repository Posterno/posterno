<?php
/**
 * Admin footer.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
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
	if ( $typenow == 'listings' ) {
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
