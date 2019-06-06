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

	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$login_page = absint( $page_option[0] );
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

	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$registration_page = absint( $page_option[0] );
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

	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$password_page = absint( $page_option[0] );
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

	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$dashboard_page = absint( $page_option[0] );
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

	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$profile_page = absint( $page_option[0] );
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

	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$listing_submission_page = absint( $page_option[0] );
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

	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$listing_submission_page = absint( $page_option[0] );
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
	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$page_id = absint( $page_option[0] );
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
	if ( is_array( $page_option ) && isset( $page_option[0] ) ) {
		$page_id = absint( $page_option[0] );
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
			'name' => esc_html__( 'Dashboard', 'posterno' ),
		],
		'listings'     => [
			'name' => esc_html__( 'Manage listings', 'posterno' ),
		],
		'edit-account' => [
			'name' => esc_html__( 'Account details', 'posterno' ),
		],
		'password'     => [
			'name' => esc_html__( 'Change password', 'posterno' ),
		],
		'privacy'      => [
			'name' => esc_html__( 'Privacy settings', 'posterno' ),
		],
		'logout'       => [
			'name' => esc_html__( 'Logout', 'posterno' ),
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
		'facebook'       => esc_html__( 'Facebook', 'posterno' ),
		'twitter'        => esc_html__( 'Twitter', 'posterno' ),
		'google-plus'    => esc_html__( 'Google+', 'posterno' ),
		'linkedin'       => esc_html__( 'LinkedIn', 'posterno' ),
		'pinterest'      => esc_html__( 'Pinterest', 'posterno' ),
		'instagram'      => esc_html__( 'Instagram', 'posterno' ),
		'tumblr'         => esc_html__( 'Tumblr', 'posterno' ),
		'flickr'         => esc_html__( 'Flickr', 'posterno' ),
		'snapchat'       => esc_html__( 'Snapchat', 'posterno' ),
		'reddit'         => esc_html__( 'Reddit', 'posterno' ),
		'youtube'        => esc_html__( 'Youtube', 'posterno' ),
		'vimeo'          => esc_html__( 'Vimeo', 'posterno' ),
		'github'         => esc_html__( 'Github', 'posterno' ),
		'dribbble'       => esc_html__( 'Dribbble', 'posterno' ),
		'behance'        => esc_html__( 'Behance', 'posterno' ),
		'soundcloud'     => esc_html__( 'SoundCloud', 'posterno' ),
		'stack-overflow' => esc_html__( 'Stack Overflow', 'posterno' ),
		'whatsapp'       => esc_html__( 'Whatsapp', 'posterno' ),
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
		'am' => esc_html__( 'AM', 'posterno' ),
		'pm' => esc_html__( 'PM', 'posterno' ),
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
		'monday'    => esc_html__( 'Monday', 'posterno' ),
		'tuesday'   => esc_html__( 'Tuesday', 'posterno' ),
		'wednesday' => esc_html__( 'Wednesday', 'posterno' ),
		'thursday'  => esc_html__( 'Thursday', 'posterno' ),
		'friday'    => esc_html__( 'Friday', 'posterno' ),
		'saturday'  => esc_html__( 'Saturday', 'posterno' ),
		'sunday'    => esc_html__( 'Sunday', 'posterno' ),
	];

	return $days;

}

/**
 * Retrieve an array with the days of the week in a shorter form.
 *
 * @return array
 */
function pno_get_days_of_the_week_short() {

	$days = [
		'monday'    => esc_html__( 'Mon', 'posterno' ),
		'tuesday'   => esc_html__( 'Tue', 'posterno' ),
		'wednesday' => esc_html__( 'Wed', 'posterno' ),
		'thursday'  => esc_html__( 'Thu', 'posterno' ),
		'friday'    => esc_html__( 'Fri', 'posterno' ),
		'saturday'  => esc_html__( 'Sat', 'posterno' ),
		'sunday'    => esc_html__( 'Sun', 'posterno' ),
	];

	return $days;

}

/**
 * Retrieve the queried listing type id during the submission process.
 *
 * @return mixed
 */
function pno_get_submission_queried_listing_type_id() {

	return isset( $_GET['listing_type_id'] ) && ! empty( sanitize_text_field( $_GET['listing_type_id'] ) ) ? absint( $_GET['listing_type_id'] ) : false;

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
		'fields'     => 'ids',
	);

	if ( $listing_type_id && pno_get_option( 'submission_categories_associated' ) ) {
		$terms_args['include'] = $categories_associated_to_type;
	}

	$cats = get_terms( 'listings-categories', $terms_args );

	$terms = pno_get_taxonomy_hierarchy_for_chain_selector( 'listings-categories', 0, $cats );

	return $terms;

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
 * Retrieve taxonomy tree formatted for the frontend submission field's Vuejs options property.
 *
 * @param string  $taxonomy the taxonomy to analyze.
 * @param integer $parent the id of the parent term to analyze.
 * @param array   $include the term ids to specifically analyze.
 * @return array
 */
function pno_get_taxonomy_hierarchy_for_chain_selector( $taxonomy, $parent = 0, $include = [] ) {

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

		$term->children = pno_get_taxonomy_hierarchy_for_chain_selector( $taxonomy, $term->term_id );

		$new_item = [
			'id'    => $term->term_id,
			'label' => $term->name,
		];

		if ( is_array( $term->children ) && ! empty( $term->children ) ) {
			$new_item['children'] = $term->children;
		}

		$children[] = $new_item;

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
		'googlemaps' => esc_html__( 'Google maps', 'posterno' ),
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

	$message = esc_html__( 'Listing successfully updated.', 'posterno' );

	if ( $status === 'yes_moderated' ) {
		$message = esc_html__( 'Listing successfully updated. An administrator will review your submission and you\'ll receive an email notification once your listing is approved.', 'posterno' );
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

/**
 * Get allowed mime types and use the key as a label.
 * This function is used within dropdowns.
 *
 * @return array
 */
function pno_get_human_readable_mime_types() {
	return array_flip( get_allowed_mime_types() );
}

/**
 * Returns true when viewing a listing taxonomy archive.
 *
 * @return boolean
 */
function pno_is_listing_taxonomy() {
	return is_tax( get_object_taxonomies( 'listings' ) );
}

/**
 * Returns the current page url including query strings.
 *
 * @return string
 */
function pno_get_full_page_url() {

	global $wp;

	return add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );

}

/**
 * Get content of a string between 2 characters.
 *
 * @param string $str the entire string.
 * @param string $from opening character.
 * @param string $to closing character.
 * @return mixed
 */
function pno_get_string_between( $str, $from, $to ) {

	$sub = substr( $str, strpos( $str, $from ) + strlen( $from ), strlen( $str ) );

	return substr( $sub, 0, strpos( $sub, $to ) );

}

/**
 * Determine if the currently logged in user can submit listings.
 *
 * @return boolean
 */
function pno_can_user_submit_listings() {

	$roles_required = pno_get_option( 'submission_requires_roles' );

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( empty( $roles_required ) ) {

		return true;

	} else {

		$user           = wp_get_current_user();
		$role           = (array) $user->roles;
		$roles_selected = [ 'administrator' ];

		foreach ( $roles_required as $single_role ) {
			$roles_selected[] = esc_attr( $single_role );
		}

		if ( ! array_intersect( (array) $user->roles, $roles_selected ) ) {
			return false;
		}
	}

	return true;

}

/**
 * Determine if listings expiration is enabled.
 *
 * @return boolean
 */
function pno_listings_can_expire() {

	$expiration = pno_get_option( 'listings_duration', false );

	return is_numeric( $expiration );

}

/**
 * Calculates and returns the listing expiry date.
 *
 * @param  int $listing_id the id of the listing.
 * @return string
 */
function pno_calculate_listing_expiry( $listing_id = false ) {

	// Get duration from the product if set...
	$duration = get_post_meta( $listing_id, 'listing_duration', true );

	// ...otherwise use the global option.
	if ( ! $duration ) {
		$duration = absint( pno_get_option( 'listings_duration' ) );
	}

	if ( $duration ) {
		return date( get_option( 'date_format' ), strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
	}

	return '';
}

/**
 * Sorty an array by using the keys order specified into another array.
 *
 * @param array $array the array to sort.
 * @param array $order_array the array that dictates the order.
 * @return array
 */
function pno_sort_array_by_array( array $array, array $order_array ) {
	$ordered = array();
	foreach ( $order_array as $key ) {
		if ( array_key_exists( $key, $array ) ) {
			$ordered[ $key ] = $array[ $key ];
			unset( $array[ $key ] );
		}
	}
	return $ordered + $array;
}

/**
 * Search a multidimensional array by a given key => value pairing.
 *
 * @param array  $array the array to search.
 * @param string $key the key where we're performing the search.
 * @param string $value the value to look for.
 * @return array
 */
function pno_search_in_array( $array, $key, $value ) {
	$results = array();

	if ( is_array( $array ) ) {
		if ( isset( $array[ $key ] ) && $array[ $key ] == $value ) {
			$results[] = $array;
		}

		foreach ( $array as $subarray ) {
			$results = array_merge( $results, pno_search_in_array( $subarray, $key, $value ) );
		}
	}

	return $results;
}

/**
 * Helper function, determine if a string starts with specific characters.
 *
 * @param string $haystack whole string to search.
 * @param string $needle the characters that needs to be found.
 * @return boolean
 */
function pno_starts_with( $haystack, $needle ) {
	$length = strlen( $needle );
	return ( substr( $haystack, 0, $length ) === $needle );
}

/**
 * Helper function, determine if a string ends with specific characters.
 *
 * @param string $haystack whole string to search.
 * @param string $needle the characters that needs to be found.
 * @return boolean
 */
function pno_ends_with( $haystack, $needle ) {
	$length = strlen( $needle );
	if ( $length === 0 ) {
		return true;
	}
	return ( substr( $haystack, -$length ) === $needle );
}

/**
 * Find the adjacent array key in an array.
 *
 * @param string $key the current key.
 * @param array  $hash the full array.
 * @param int    $increment +1 or -1 for next or prev.
 * @return mixed
 */
function pno_get_adjacent_array_key( $key, $hash = array(), $increment ) {
	$keys        = array_keys( $hash );
	$found_index = array_search( $key, $keys );
	if ( $found_index === false ) {
		return false;
	}
	$newindex = $found_index + $increment;
	return ( $newindex > 0 && $newindex < count( $hash ) ) ? $keys[ $newindex ] : false;
}

/**
 * Helper function to recursively map a function to multidimensional array elements.
 *
 * @param string $f function name.
 * @param array  $xs data to filter.
 * @return array
 */
function pno_array_map_recursive( $f, $xs ) {
	$out = [];
	foreach ( $xs as $k => $x ) {
		$out[ $k ] = ( is_array( $x ) ) ? pno_array_map_recursive( $f, $x ) : $f( $x );
	}
	return $out;
}

/**
 * Checks whether function is disabled.
 *
 * @param string $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function pno_is_func_disabled( $function ) {
	$disabled = explode( ',', ini_get( 'disable_functions' ) );
	return in_array( $function, $disabled );
}

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @param string $str File name.
 * @return mixed File extension.
 */
function pno_get_file_extension( $str ) {
	$parts = explode( '.', $str );
	return end( $parts );
}

/**
 * Given an object or array of objects, convert them to arrays
 *
 * @param object|array $object An object or an array of objects.
 * @return array
 */
function pno_object_to_array( $object = array() ) {
	if ( empty( $object ) || ( ! is_object( $object ) && ! is_array( $object ) ) ) {
		return $object;
	}
	if ( is_array( $object ) ) {
		$return = array();
		foreach ( $object as $item ) {
			$return[] = pno_object_to_array( $item );
		}
	} else {
		$return = get_object_vars( $object );
		// Now look at the items that came back and convert any nested objects to arrays.
		foreach ( $return as $key => $value ) {
			$value          = ( is_array( $value ) || is_object( $value ) ) ? pno_object_to_array( $value ) : $value;
			$return[ $key ] = $value;
		}
	}
	return $return;
}

/**
 * Abstraction for WordPress cron checking, to avoid code duplication.
 *
 * @return boolean
 */
function pno_doing_cron() {

	if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
		return true;
	} elseif ( defined( 'DOING_CRON' ) && ( true === DOING_CRON ) ) {
		return true;
	}

	return false;
}

/**
 * Retrieve file path from url.
 *
 * @param string $url url.
 * @return string
 */
function pno_content_url_to_local_path( $url ) {

	$path = $_SERVER['DOCUMENT_ROOT'] . wp_parse_url( $url, PHP_URL_PATH );

	if ( ! pno_starts_with( $path, WP_CONTENT_DIR ) ) {
		return false;
	}

	return $path;
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 *
 * @param int $limit Time limit.
 */
function pno_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
		@set_time_limit( $limit ); // @codingStandardsIgnoreLine
	}
}

/**
 * Define a constant if it is not already defined.
 *
 * @param string $name  Constant name.
 * @param mixed  $value Value.
 */
function pno_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Wrapper for nocache_headers which also disables page caching.
 *
 * @return void
 */
function pno_nocache_headers() {
	\PNO\Cache\Helper::set_nocache_constants();
	nocache_headers();
}
