<?php
/**
 * List of functions used all around within the plugin.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly..
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

/**
 * Retrieve the ID number of the selected registration page.
 *
 * @return mixed
 */
function pno_get_registration_page_id() {

	$registration_page = false;
	$page_option       = pno_get_option( 'registration_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$registration_page = absint( $page_option['value'] );
	}

	return $registration_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_password_recovery_page_id() {

	$password_page = false;
	$page_option   = pno_get_option( 'password_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$password_page = absint( $page_option['value'] );
	}

	return $password_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_dashboard_page_id() {

	$dashboard_page = false;
	$page_option    = pno_get_option( 'dashboard_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$dashboard_page = absint( $page_option['value'] );
	}

	return $dashboard_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_profile_page_id() {

	$profile_page = false;
	$page_option  = pno_get_option( 'profile_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$profile_page = absint( $page_option['value'] );
	}

	return $profile_page;

}

/**
 * Retrieve the id number of the selected listing submission page in the admin panel.
 *
 * @return string|boolean
 */
function pno_get_listing_submission_page_id() {

	$listing_submission_page = false;
	$page_option             = pno_get_option( 'submission_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$listing_submission_page = absint( $page_option['value'] );
	}

	return $listing_submission_page;
}

/**
 * Retrieve the id number of the selected listing editing page in the admin panel.
 *
 * @return string|boolean
 */
function pno_get_listing_editing_page_id() {

	$listing_submission_page = false;
	$page_option             = pno_get_option( 'editing_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$listing_submission_page = absint( $page_option['value'] );
	}

	return $listing_submission_page;

}

/**
 * Retrieve the id number of the selected success page to display to users
 * after they've successfully submitted a listing.
 *
 * @return mixed
 */
function pno_get_listing_success_redirect_page_id() {

	$page_id     = false;
	$page_option = pno_get_option( 'listing_submission_redirect' );
	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$page_id = absint( $page_option['value'] );
	}

	return $page_id;

}

/**
 * Retrieve the id number of the selected success page to display to users
 * after they've successfully edited a listing.
 *
 * @return mixed
 */
function pno_get_listing_success_edit_redirect_page_id() {

	$page_id     = false;
	$page_option = pno_get_option( 'listing_editing_redirect' );
	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$page_id = absint( $page_option['value'] );
	}

	return $page_id;

}

/**
 * Defines a list of navigation items for the dashboard page.
 *
 * @return array
 */
function pno_get_dashboard_navigation_items() {

	$items = [
		'dashboard'    => [
			'name' => esc_html__( 'Dashboard' ),
		],
		'listings'     => [
			'name' => esc_html__( 'Manage listings' ),
		],
		'edit-account' => [
			'name' => esc_html__( 'Account details' ),
		],
		'password'     => [
			'name' => esc_html__( 'Change password' ),
		],
		'privacy'      => [
			'name' => esc_html__( 'Privacy settings' ),
		],
		'logout'       => [
			'name' => esc_html__( 'Logout' ),
		],
	];

	/**
	 * Allows developers to register or deregister navigation items
	 * for the dashboard menu.
	 *
	 * @param array $items
	 */
	$items = apply_filters( 'pno_dashboard_navigation_items', $items );

	$first                       = key( $items );
	$items[ $first ]['is_first'] = true;

	return $items;

}

/**
 * Retrieve a list of registered social medias for Posterno.
 *
 * @return array
 */
function pno_get_registered_social_media() {

	$socials = [
		'facebook'       => esc_html__( 'Facebook' ),
		'twitter'        => esc_html__( 'Twitter' ),
		'google-plus'    => esc_html__( 'Google+' ),
		'linkedin'       => esc_html__( 'LinkedIn' ),
		'pinterest'      => esc_html__( 'Pinterest' ),
		'instagram'      => esc_html__( 'Instagram' ),
		'tumblr'         => esc_html__( 'Tumblr' ),
		'flickr'         => esc_html__( 'Flickr' ),
		'snapchat'       => esc_html__( 'Snapchat' ),
		'reddit'         => esc_html__( 'Reddit' ),
		'youtube'        => esc_html__( 'Youtube' ),
		'vimeo'          => esc_html__( 'Vimeo' ),
		'github'         => esc_html__( 'Github' ),
		'dribbble'       => esc_html__( 'Dribbble' ),
		'behance'        => esc_html__( 'Behance' ),
		'soundcloud'     => esc_html__( 'SoundCloud' ),
		'stack-overflow' => esc_html__( 'Stack Overflow' ),
		'whatsapp'       => esc_html__( 'Whatsapp' ),
	];

	/**
	 * Allows developers to register additional social media networks for listings.
	 *
	 * @param array $socials the current list of social medias.
	 * @return array
	 */
	$socials = apply_filters( 'pno_registered_social_media', $socials );

	asort( $socials );

	return $socials;

}

/**
 * Get timings for the opening hours selector.
 *
 * @return array
 */
function pno_get_am_pm_declaration() {

	$timings = [
		'am' => esc_html__( 'AM' ),
		'pm' => esc_html__( 'PM' ),
	];

	return $timings;
}

/**
 * Retrieve an array with the days of the week.
 *
 * @return array
 */
function pno_get_days_of_the_week() {

	$days = [
		'monday'    => esc_html__( 'Monday' ),
		'tuesday'   => esc_html__( 'Tuesday' ),
		'wednesday' => esc_html__( 'Wednesday' ),
		'thursday'  => esc_html__( 'Thursday' ),
		'friday'    => esc_html__( 'Friday' ),
		'saturday'  => esc_html__( 'Saturday' ),
		'sunday'    => esc_html__( 'Sunday' ),
	];

	return $days;

}

/**
 * Retrieve the queried listing type id during the submission process.
 *
 * @return mixed
 */
function pno_get_submission_queried_listing_type_id() {

	return isset( $_POST['listing_type_id'] ) && ! empty( sanitize_text_field( $_POST['listing_type_id'] ) ) ? absint( $_POST['listing_type_id'] ) : false;

}

/**
 * Retrieve the list of categories for the listings submission form.
 *
 * @param mixed $listing_type_id query categories by associated listing type id.
 * @return array
 */
function pno_get_listings_categories_for_submission_selection( $listing_type_id = false ) {

	$categories = [];

	$categories_associated_to_type = carbon_get_term_meta( $listing_type_id, 'associated_categories' );

	$terms_args = array(
		'hide_empty' => false,
		'number'     => 999,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'parent'     => 0,
	);

	if ( $listing_type_id && pno_get_option( 'submission_categories_associated' ) ) {
		$terms_args['include'] = $categories_associated_to_type;
	}

	$terms = get_terms( 'listings-categories', $terms_args );

	if ( ! empty( $terms ) ) {
		foreach ( $terms as $listing_category ) {
			$categories[] = absint( $listing_category->term_id );
		}
	}

	return $categories;

}

/**
 * Retrieve taxonomy tree.
 *
 * @param string  $taxonomy the taxonomy to analyze.
 * @param integer $parent the id of the parent term to analyze.
 * @param array   $include the term ids to specifically analyze.
 * @return array
 */
function pno_get_taxonomy_hierarchy( $taxonomy, $parent = 0, $include = [] ) {

	$taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;

	$terms = get_terms(
		$taxonomy,
		[
			'parent'     => $parent,
			'hide_empty' => false,
			'include'    => $include,
		]
	);

	$children = array();

	foreach ( $terms as $term ) {
		$term->children             = pno_get_taxonomy_hierarchy( $taxonomy, $term->term_id );
		$children[ $term->term_id ] = $term;
	}

	return $children;

}

/**
 * Retrieve the most parent term of a given term.
 *
 * @param string $term_id the id of the term to verify.
 * @param string $taxonomy the taxonomy of the term to verify.
 * @return mixed
 */
function pno_get_term_top_most_parent( $term_id, $taxonomy ) {

	// start from the current term.
	$parent = get_term_by( 'id', $term_id, $taxonomy );

	// climb up the hierarchy until we reach a term with parent = '0'.
	while ( $parent->parent != '0' ) {
		$term_id = $parent->parent;
		$parent  = get_term_by( 'id', $term_id, $taxonomy );
	}

	return $parent;
}

/**
 * Retrieve the list of registered map providers in Posterno.
 *
 * @return array
 */
function pno_get_registered_maps_providers() {

	$providers = [
		'googlemaps' => esc_html__( 'Google maps' ),
	];

	/**
	 * Allow developers to customize the registered maps providers for Posterno.
	 *
	 * @param array $provides the list of currently registered providers.
	 * @return array
	 */
	return apply_filters( 'pno_registered_maps_providers', $providers );

}

/**
 * Determine if a user has submitted listings.
 *
 * @param string $user_id the user to check.
 * @return mixed
 */
function pno_user_has_submitted_listings( $user_id ) {

	global $wpdb;

	if ( ! $user_id ) {
		return;
	}

	$user_id = absint( $user_id );

	return wp_cache_remember(
		"user_has_submitted_listings_{$user_id}",
		function () use ( $wpdb, $user_id ) {
			$where = "WHERE ( ( post_type = 'listings' AND ( post_status = 'publish' OR post_status = 'pending' OR post_status = 'expired' ) ) ) AND post_author = {$user_id}";
			$count = $wpdb->get_var("SELECT ID FROM $wpdb->posts $where LIMIT 1"); //phpcs:ignore
			return $count;
		}
	);

}

/**
 * Retrieve the success message displayed to users after editing a listing.
 * The message changes based on the moderation status set in the settings panel.
 *
 * @return string
 */
function pno_get_listing_updated_message() {

	$status = pno_published_listings_can_be_edited();

	$message = esc_html__( 'Listing successfully updated.' );

	if ( $status === 'yes_moderated' ) {
		$message = esc_html__( 'Listing successfully updated. An administrator will review your submission and you\'ll receive an email notification once your listing is approved.' );
	}

	/**
	 * Allow developers to adjust the success message displayed to users after editing a listing.
	 *
	 * @param string $message the message to display.
	 * @param string $status the type of moderation status selected in the admin panel.
	 * @return string
	 */
	return apply_filters( 'pno_listing_updated_success_message', $message, $status );

}


function wpp_dropdown_categories( $args = '' ) {
	$defaults = array(
		'show_option_all'   => '',
		'show_option_none'  => '',
		'orderby'           => 'id',
		'order'             => 'ASC',
		'show_count'        => 0,
		'hide_empty'        => 1,
		'child_of'          => 0,
		'exclude'           => '',
		'echo'              => 1,
		'selected'          => 0,
		'hierarchical'      => 0,
		'name'              => 'cat',
		'id'                => '',
		'class'             => 'postform',
		'depth'             => 0,
		'tab_index'         => 0,
		'taxonomy'          => 'category',
		'hide_if_empty'     => false,
		'option_none_value' => -1,
		'value_field'       => 'term_id',
		'required'          => false,
	);
	$defaults['selected'] = ( is_category() ) ? get_query_var( 'cat' ) : 0;
	// Back compat.
	if ( isset( $args['type'] ) && 'link' == $args['type'] ) {
		_deprecated_argument(
			__FUNCTION__,
			'3.0.0',
			/* translators: 1: "type => link", 2: "taxonomy => link_category" */
			sprintf(
				__( '%1$s is deprecated. Use %2$s instead.' ),
				'<code>type => link</code>',
				'<code>taxonomy => link_category</code>'
			)
		);
		$args['taxonomy'] = 'link_category';
	}
	$r                 = wp_parse_args( $args, $defaults );
	$option_none_value = $r['option_none_value'];
	if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}
	$tab_index = $r['tab_index'];
	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 ) {
		$tab_index_attribute = " tabindex=\"$tab_index\"";
	}
	// Avoid clashes with the 'name' param of get_terms().
	$get_terms_args = $r;
	unset( $get_terms_args['name'] );

	$get_terms_args['exclude'] = 30;

	$categories = get_terms( $r['taxonomy'], $get_terms_args );
	$name     = esc_attr( $r['name'] );
	$class    = esc_attr( $r['class'] );
	$id       = $r['id'] ? esc_attr( $r['id'] ) : $name;
	$required = $r['required'] ? 'required' : '';
	if ( ! $r['hide_if_empty'] || ! empty( $categories ) ) {
		$output = "<select $required name='$name' id='$id' class='$class' $tab_index_attribute>\n";
	} else {
		$output = '';
	}
	if ( empty( $categories ) && ! $r['hide_if_empty'] && ! empty( $r['show_option_none'] ) ) {
		/**
		 * Filters a taxonomy drop-down display element.
		 *
		 * A variety of taxonomy drop-down display elements can be modified
		 * just prior to display via this filter. Filterable arguments include
		 * 'show_option_none', 'show_option_all', and various forms of the
		 * term name.
		 *
		 * @since 1.2.0
		 *
		 * @see wp_dropdown_categories()
		 *
		 * @param string       $element  Category name.
		 * @param WP_Term|null $category The category object, or null if there's no corresponding category.
		 */
		$show_option_none = apply_filters( 'list_cats', $r['show_option_none'], null );
		$output          .= "\t<option value='" . esc_attr( $option_none_value ) . "' selected='selected'>$show_option_none</option>\n";
	}
	if ( ! empty( $categories ) ) {
		if ( $r['show_option_all'] ) {
			/** This filter is documented in wp-includes/category-template.php */
			$show_option_all = apply_filters( 'list_cats', $r['show_option_all'], null );
			$selected        = ( '0' === strval( $r['selected'] ) ) ? " selected='selected'" : '';
			$output         .= "\t<option value='0'$selected>$show_option_all</option>\n";
		}
		if ( $r['show_option_none'] ) {
			/** This filter is documented in wp-includes/category-template.php */
			$show_option_none = apply_filters( 'list_cats', $r['show_option_none'], null );
			$selected         = selected( $option_none_value, $r['selected'], false );
			$output          .= "\t<option value='" . esc_attr( $option_none_value ) . "'$selected>$show_option_none</option>\n";
		}
		if ( $r['hierarchical'] ) {
			$depth = $r['depth'];  // Walk the full depth.
		} else {
			$depth = -1; // Flat.
		}
		$output .= walk_category_dropdown_tree( $categories, $depth, $r );
	}
	if ( ! $r['hide_if_empty'] || ! empty( $categories ) ) {
		$output .= "</select>\n";
	}
	/**
	 * Filters the taxonomy drop-down output.
	 *
	 * @since 2.1.0
	 *
	 * @param string $output HTML output.
	 * @param array  $r      Arguments used to build the drop-down.
	 */
	$output = apply_filters( 'wp_dropdown_cats', $output, $r );
	if ( $r['echo'] ) {
		echo $output;
	}
	return $output;
}
