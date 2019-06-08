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
 * @param  string $footer_text The existing footer text.
 * @return string
 */
function pno_admin_rate_us( $footer_text ) {

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
		'admin_page_posterno-options[listings]',
		'listings_page_posterno-listings-schema',
		'tools_page_posterno-tools',
		'listings_page_posterno-addons',
		'listings_page_schemas_exporter',
		'listings_page_emails_exporter',
		'listings_page_listings_fields_exporter',
		'listings_page_profile_fields_exporter',
		'listings_page_registration_fields_exporter',
		'listings_page_taxonomy_exporter',
		'listings_page_listings_exporter',
	];

	if ( in_array( $screen->id, $checks ) ) {
		$rate_text = sprintf(
			/* translators: %s: Link to 5 star rating */
			__( 'If you like <strong>Posterno</strong> please rate us on %s. It takes a minute and helps a lot. Thanks in advance!', 'posterno' ),
			'<a href="https://wordpress.org/support/view/plugin-reviews/posterno?filter=5#postform" target="_blank" class="posterno-rating-link" style="text-decoration:none;" data-rated="' . esc_attr__( 'Thanks :)', 'posterno' ) . '">WordPress.org &#9733;&#9733;&#9733;&#9733;&#9733;</a>'
		);

		return $rate_text;
	} else {
		return $footer_text;
	}
}
add_filter( 'admin_footer_text', 'pno_admin_rate_us' );
