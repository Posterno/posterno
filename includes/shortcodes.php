<?php
/**
 * Shortcodes definition
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display a login link.
 *
 * @param array  $atts attributes list of the shortcode.
 * @param string $content content added within the shortcode.
 * @return string
 */
function pno_login_link( $atts, $content = null ) {
	// phpcs:ignore
	extract(
		shortcode_atts(
			array(
				'redirect' => '',
				'label'    => esc_html__( 'Login', 'posterno' ),
			),
			$atts
		)
	);
	if ( is_user_logged_in() ) {
		$output = '';
	} else {
		$url    = wp_login_url( $redirect );
		$output = '<a href="' . esc_url( $url ) . '" class="pno-login-link">' . esc_html( $label ) . '</a>';
	}
	return $output;
}
add_shortcode( 'pno_login_link', 'pno_login_link' );

/**
 * Display a logout link.
 *
 * @param array  $atts attributes list of the shortcode.
 * @param string $content content added within the shortcode.
 * @return string
 */
function pno_logout_link( $atts, $content = null ) {
	// phpcs:ignore
	extract(
		shortcode_atts(
			array(
				'redirect' => '',
				'label'    => esc_html__( 'Logout', 'posterno' ),
			),
			$atts
		)
	);
	$output = '';
	if ( is_user_logged_in() ) {
		$output = '<a href="' . esc_url( wp_logout_url( $redirect ) ) . '">' . esc_html( $label ) . '</a>';
	}
	return $output;
}
add_shortcode( 'pno_logout_link', 'pno_logout_link' );

/**
 * Displays the dashboard for the listings.
 *
 * @return string
 */
function pno_dashboard() {

	ob_start();

	posterno()->templates->get_template_part( 'dashboard' );

	return ob_get_clean();

}
add_shortcode( 'pno_dashboard', 'pno_dashboard' );

/**
 * Displays the account customization form.
 *
 * @return string
 */
function pno_account_form() {

	ob_start();

	if ( is_user_logged_in() ) {
		//phpcs:ignore
		echo posterno()->forms->get_form( 'account' );
	}

	return ob_get_clean();

}
// add_shortcode( 'pno_account_customization_form', 'pno_account_form' );

/**
 * Displays the listing submission form.
 *
 * @return string
 */
function pno_listing_submission_form() {

	ob_start();

	$roles_required = pno_get_option( 'submission_requires_roles' );

	$restricted = false;

	// Display error message if specific roles are required to access the page.
	if ( ! pno_can_user_submit_listings() ) {
		$restricted = true;
	}

	/**
	 * Allow developers to add custom access restrictions to the submission form.
	 *
	 * @param bool $restricted true or false.
	 * @return bool|string
	 */
	$restricted = apply_filters( 'pno_submission_form_is_restricted', $restricted );

	if ( $restricted ) {

		/**
		 * Allow developers to customize the restriction message for the submission form.
		 *
		 * @param string $message the restriction message.
		 * @param bool|string $restricted wether it's restricted or not and what type of restriction.
		 */
		$message = apply_filters( 'pno_submission_restriction_message', esc_html__( 'Access to this page is restricted.', 'posterno' ), $restricted );

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'warning',
					'message' => $message,
				]
			)
			->get_template_part( 'message' );

	} else {

		//phpcs:ignore
		echo posterno()->forms->get_form( 'listing-submission' );

	}

	return ob_get_clean();

}
add_shortcode( 'pno_listing_submission_form', 'pno_listing_submission_form' );

/**
 * Displays the listing editing form.
 *
 * @return string
 */
function pno_listing_editing_form() {

	ob_start();

	$user_id    = is_user_logged_in() ? get_current_user_id() : false;
	$listing_id = pno_get_queried_listing_editable_id();

	if (
		! is_user_logged_in() ||
		! is_page( pno_get_listing_editing_page_id() ) ||
		! $listing_id ||
		! pno_is_user_owner_of_listing( $user_id, $listing_id ) ||
		pno_is_listing_expired( $listing_id ) ||
		( pno_is_listing_pending_approval( $listing_id ) && pno_is_user_owner_of_listing( $user_id, $listing_id ) && ! pno_pending_listings_can_be_edited() ) ) {

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'warning',
					'message' => esc_html__( 'You are not authorized to access this page.', 'posterno' ),
				]
			)
			->get_template_part( 'message' );

	} else {

		//phpcs:ignore
		echo posterno()->forms->get_form( 'listing-edit' );

	}

	return ob_get_clean();

}
add_shortcode( 'pno_listing_editing_form', 'pno_listing_editing_form' );

/**
 * Displays the profile page.
 *
 * @return string
 */
function pno_profile() {

	ob_start();

	posterno()->templates
		->set_template_data(
			[
				'user_id' => pno_get_queried_user_id(),
			]
		)
		->get_template_part( 'profile' );

	return ob_get_clean();

}
add_shortcode( 'pno_profile', 'pno_profile' );

/**
 * Displays a list of listings types.
 *
 * @param array  $atts attributes sent through the shortcode.
 * @param string $content content of the shortcode.
 * @return string
 */
function pno_listings_types_shortcode( $atts, $content = null ) {

	ob_start();

	posterno()->templates
		->set_template_data( $atts )
		->get_template_part( 'shortcodes/listing-types-list' );

	return ob_get_clean();

}
add_shortcode( 'pno_listings_types', 'pno_listings_types_shortcode' );

/**
 * Displays a list of recent listings.
 *
 * @param array  $atts attributes sent through the shortcode.
 * @param string $content content of the shortcode.
 * @return string
 */
function pno_recent_listings_shortcode( $atts, $content = null ) {

	ob_start();

	posterno()->templates
		->set_template_data( $atts )
		->get_template_part( 'shortcodes/recent-listings' );

	return ob_get_clean();

}
add_shortcode( 'pno_recent_listings', 'pno_recent_listings_shortcode' );

/**
 * Displays a list of featured listings.
 *
 * @param array  $atts attributes sent through the shortcode.
 * @param string $content content of the shortcode.
 * @return string
 */
function pno_featured_listings_shortcode( $atts, $content = null ) {

	ob_start();

	posterno()->templates
		->set_template_data( $atts )
		->get_template_part( 'shortcodes/featured-listings' );

	return ob_get_clean();

}
add_shortcode( 'pno_featured_listings', 'pno_featured_listings_shortcode' );

/**
 * Displays a list of listings categories.
 *
 * @param array  $atts attributes sent through the shortcode.
 * @param string $content content of the shortcode.
 * @return string
 */
function pno_listings_categories_shortcode( $atts, $content = null ) {

	ob_start();

	posterno()->templates
		->set_template_data( $atts )
		->get_template_part( 'shortcodes/listings-categories' );

	return ob_get_clean();

}
add_shortcode( 'pno_listings_categories', 'pno_listings_categories_shortcode' );

/**
 * Displays a list of listings locations.
 *
 * @param array  $atts attributes sent through the shortcode.
 * @param string $content content of the shortcode.
 * @return string
 */
function pno_listings_locations_shortcode( $atts, $content = null ) {

	ob_start();

	posterno()->templates
		->set_template_data( $atts )
		->get_template_part( 'shortcodes/listings-locations' );

	return ob_get_clean();

}
add_shortcode( 'pno_listings_locations', 'pno_listings_locations_shortcode' );

/**
 * Displays a list of recent listings together with a map, filters and pagination.
 *
 * @param array  $atts attributes sent through the shortcode.
 * @param string $content content of the shortcode.
 * @return string
 */
function pno_listings_page_shortcode( $atts, $content = null ) {

	wp_enqueue_script( 'pno-listings-page-googlemap' );

	ob_start();

	posterno()->templates
		->set_template_data( $atts )
		->get_template_part( 'shortcodes/listings-page' );

	return ob_get_clean();

}
add_shortcode( 'pno_listings_page', 'pno_listings_page_shortcode' );
