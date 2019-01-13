<?php
/**
 * All listings related functionalities of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Store opening hours of a given listing by day into the database.
 *
 * @param mixed  $listing_id ID of the listing to update.
 * @param string $day string of the day we're saving @see pno_get_days_of_the_week().
 * @param string $slot opening or closing.
 * @param string $time submitted time.
 * @return void
 */
function pno_update_listing_opening_hours_by_day( $listing_id = false, $day = '', $slot = '', $time = '' ) {

	if ( ! $listing_id || empty( $day ) || empty( $slot ) ) {
		return;
	}

	if ( ! array_key_exists( $day, pno_get_days_of_the_week() ) ) {
		return;
	}

	$existing_timings = get_post_meta( $listing_id, '_listing_opening_hours', true );

	if ( empty( $existing_timings ) || ! is_array( $existing_timings ) ) {
		$existing_timings = [];
	}

	$slots = [
		'opening',
		'closing',
	];

	if ( ! in_array( $slot, $slots ) ) {
		return;
	}

	// If we receive an empty time, then we remove the slot from the storage array.
	if ( empty( $time ) ) {
		if ( isset( $existing_timings[ $day ][ $slot ] ) ) {
			unset( $existing_timings[ $day ][ $slot ] );
		}
	} else {

		$time = is_array( $time ) ? $time : sanitize_text_field( $time );

		$existing_timings[ $day ][ $slot ] = $time;

	}

	update_post_meta( $listing_id, '_listing_opening_hours', $existing_timings );

}

/**
 * Store additional opening hours for a specific day of the week for listings.
 *
 * @param mixed  $listing_id the listing id.
 * @param string $day string of day of the week.
 * @param array  $timings list of closing and opening times.
 * @return void
 */
function pno_update_listing_additional_opening_hours_by_day( $listing_id = false, $day = '', $timings = [] ) {

	if ( ! $listing_id || empty( $day ) ) {
		return;
	}

	if ( ! array_key_exists( $day, pno_get_days_of_the_week() ) ) {
		return;
	}

	$existing_timings = get_post_meta( $listing_id, '_listing_opening_hours', true );

	if ( empty( $existing_timings ) || ! is_array( $existing_timings ) ) {
		return;
	}

	if ( ! isset( $existing_timings[ $day ] ) ) {
		return;
	}

	if ( empty( $timings ) ) {

		if ( isset( $existing_timings[ $day ]['additional_times'] ) ) {
			unset( $existing_timings[ $day ]['additional_times'] );
		}
	} else {
		$existing_timings[ $day ]['additional_times'] = $timings;
	}

	update_post_meta( $listing_id, '_listing_opening_hours', $existing_timings );

}

/**
 * Store the selected hours of operation type together with the opening hours list.
 *
 * @param string $listing_id the listing we're going to update.
 * @param string $day the day that we're going to update.
 * @param string $operation the type of operation selected.
 * @return void
 */
function pno_update_listing_hours_of_operation( $listing_id, $day, $operation ) {

	if ( ! $listing_id || empty( $day ) || empty( $operation ) ) {
		return;
	}

	if ( ! array_key_exists( $day, pno_get_days_of_the_week() ) ) {
		return;
	}

	$existing_timings = get_post_meta( $listing_id, '_listing_opening_hours', true );

	if ( empty( $existing_timings ) || ! is_array( $existing_timings ) ) {
		return;
	}

	$existing_timings[ $day ]['operation'] = sanitize_text_field( $operation );

	update_post_meta( $listing_id, '_listing_opening_hours', $existing_timings );

}

/**
 * Assign a listing type to a given listing.
 *
 * @param string|int $listing_id the id of the listing.
 * @param string|int $type_id the id of the taxonomy (listing type) that we're going to add.
 * @return void
 */
function pno_assign_type_to_listing( $listing_id, $type_id ) {

	if ( ! $listing_id || ! $type_id ) {
		return;
	}

	wp_set_object_terms( absint( $listing_id ), absint( $type_id ), 'listings-types' );

}

/**
 * Retrieve the listings submitted by a specific user given a user id.
 *
 * @param string $user_id the user for which we're going to retrieve listings.
 * @return WP_Query
 */
function pno_get_user_submitted_listings( $user_id ) {

	if ( ! $user_id ) {
		return false;
	}

	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

	$query_args = [
		'post_type'      => 'listings',
		'posts_per_page' => absint( pno_get_listings_per_page_dashboard() ),
		'author'         => absint( $user_id ),
		'post_status'    => [ 'publish', 'pending' ],
		'fields'         => 'ids',
		'paged'          => $paged,
	];

	// Detect if a status has been selected within the dashboard page.
	if ( isset( $_GET['listing_status'] ) && ! empty( $_GET['listing_status'] ) && $_GET['listing_status'] !== 'all' && wp_verify_nonce( $_GET['_wpnonce'], 'verify_listings_dashboard_status' ) ) {
		$query_args['post_status'] = pno_get_dashboard_active_listings_status();
	}

	/**
	 * Allow developers to customize the query arguments when
	 * retrieving listings submitted by a specific user.
	 *
	 * @param array $query_args WP_Query args array.
	 * @param string $user_id the id number of the queried user.
	 */
	$query_args = apply_filters( 'pno_user_submitted_listings_query_args', $query_args, $user_id );

	$found_listings = new WP_Query( $query_args );

	return $found_listings;

}

/**
 * Displays the title for the listing.
 *
 * @param int|WP_Post $post listing post object or post id.
 * @return void
 */
function pno_the_listing_title( $post = null ) {
	$listing_title = pno_get_the_listing_title( $post );
	if ( $listing_title ) {
		echo wp_kses_post( $listing_title );
	}
}

/**
 * Gets the title for the listing.
 *
 * @param int|WP_Post $post (default: null).
 * @return string|bool|null
 */
function pno_get_the_listing_title( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'listings' !== $post->post_type ) {
		return null;
	}
	$title = wp_strip_all_tags( get_the_title( $post ) );
	/**
	 * Filter for the listing title.
	 *
	 * @param string      $title Title to be filtered.
	 * @param int|WP_Post $post
	 */
	return apply_filters( 'pno_the_listing_title', $title, $post );
}

/**
 * Displays the published date of the listing.
 *
 * @param int|WP_Post $post (default: null).
 */
function pno_the_listing_publish_date( $post = null ) {
	$date_format = pno_get_option( 'listing_date_format' );
	if ( 'default' === $date_format ) {
		$display_date = date_i18n( get_option( 'date_format' ), get_post_time( 'U', false, $post ) );
	} else {
		// translators: Placeholder %s is the relative, human readable time since the listing listing was posted.
		$display_date = sprintf( esc_html__( 'Posted %s ago' ), human_time_diff( get_post_time( 'U', false, $post ), current_time( 'timestamp' ) ) );
	}
	echo '<time datetime="' . esc_attr( get_post_time( 'Y-m-d', false, $post ) ) . '">' . wp_kses_post( $display_date ) . '</time>';
}

/**
 * Gets the published date of the listing.
 *
 * @param int|WP_Post $post (default: null).
 * @return string|int|false
 */
function pno_get_the_listing_publish_date( $post = null ) {
	$date_format = pno_get_option( 'listing_date_format' );
	if ( 'default' === $date_format ) {
		return get_post_time( get_option( 'date_format' ) );
	} else {
		// translators: Placeholder %s is the relative, human readable time since the listing listing was posted.
		return sprintf( __( 'Posted %s ago' ), human_time_diff( get_post_time( 'U', false, $post ), current_time( 'timestamp' ) ) );
	}
}

/**
 * Displays the expire date of the listing.
 *
 * @param int|WP_Post $post (default: null).
 * @return void
 */
function pno_the_listing_expire_date( $post = null ) {

	$expires = pno_get_the_listing_expire_date( $post ) ? pno_get_the_listing_expire_date( $post ) : '&ndash;';

	echo '<time datetime="' . esc_attr( $expires ) . '">' . wp_kses_post( $expires ) . '</time>';

}

/**
 * Gets the expire date of the listing.
 *
 * @param int|WP_Post $post (default: null).
 * @return string|int|false
 */
function pno_get_the_listing_expire_date( $post = null ) {

	$expires = get_post_meta( $post, '_listing_expires', true );

	return esc_html( $expires ? date_i18n( get_option( 'date_format' ), strtotime( $expires ) ) : false );

}

/**
 * Retrieve the list of registered post statuses for listings,
 * minus the draft status that's not used on the fronted.
 *
 * @return array
 */
function pno_get_dashboard_listings_statuses() {

	$registered_statuses = pno_get_listing_post_statuses();

	if ( isset( $registered_statuses['draft'] ) ) {
		unset( $registered_statuses['draft'] );
	}

	return $registered_statuses;

}

/**
 * Detect the currently active listing status.
 *
 * @return string
 */
function pno_get_dashboard_active_listings_status() {

	$statuses      = pno_get_dashboard_listings_statuses();
	$active_status = 'all';

	if ( isset( $_GET['listing_status'] ) && ! empty( $_GET['listing_status'] ) ) { //phpcs:ignore
		$status = sanitize_key( $_GET['listing_status'] );
		if ( isset( $statuses[ $status ] ) ) {
			$active_status = $status;
		}
	}

	return $active_status;

}

/**
 * Retrieve the url of a given listing status filter.
 * This is used within the listings dashboard.
 *
 * @param boolean $status_key the post status we're using to filter.
 * @return mixed
 */
function pno_get_dashboard_listing_status_filter_url( $status_key = false ) {

	if ( ! $status_key ) {
		return;
	}

	$url = wp_nonce_url( pno_get_dashboard_navigation_item_url( 'listings' ), 'verify_listings_dashboard_status' );
	$url = add_query_arg(
		[
			'listing_status' => sanitize_key( $status_key ),
		],
		$url
	);

	return $url;

}

/**
 * Retrieve the amount of listings to display within the dashboard page.
 *
 * @return string
 */
function pno_get_listings_per_page_dashboard() {

	$amount = ! empty( pno_get_option( 'listings_per_page_dashboard' ) ) ? pno_get_option( 'listings_per_page_dashboard' ) : 10;

	return $amount;

}

/**
 * Retrieve the list of registered listings actions.
 *
 * @return array
 */
function pno_get_listings_actions() {

	$actions = [
		'edit'   => [
			'title'    => esc_html__( 'Edit' ),
			'priority' => 1,
		],
		'view'   => [
			'title'    => esc_html__( 'View' ),
			'priority' => 2,
		],
		'delete' => [
			'title'    => esc_html__( 'Delete' ),
			'priority' => 100,
		],
	];

	if ( ! pno_get_option( 'listing_allow_editing' ) ) {
		unset( $actions['edit'] );
	}

	if ( ! pno_get_option( 'listing_allow_delete' ) ) {
		unset( $actions['delete'] );
	}

	/**
	 * Allow developers to extend the list of actions for listings.
	 *
	 * @param array $actions the list of registered actions.
	 * @return array
	 */
	$actions = apply_filters( 'pno_listings_actions', $actions );

	if ( ! empty( $actions ) ) {
		uasort( $actions, 'pno_sort_array_by_priority' );
	}

	return $actions;

}

/**
 * Retrieve the url of a given listing action.
 *
 * @param string|int $listing_id the listing id.
 * @param string|int $action_id the action key.
 * @return string
 */
function pno_get_listing_action_url( $listing_id, $action_id ) {

	if ( ! $listing_id || ! $action_id ) {
		return;
	}

	$url = wp_nonce_url( pno_get_dashboard_navigation_item_url( 'listings' ), 'verify_listing_action' );
	$url = add_query_arg(
		[
			'listing_action' => sanitize_key( $action_id ),
			'listing_id'     => absint( $listing_id ),
		],
		$url
	);

	return $url;

}

/**
 * Delete a listing given an ID.
 *
 * @param integer $listing_id the listing to remove.
 * @return void
 */
function pno_delete_listing( $listing_id = 0 ) {

	$force_delete = pno_get_option( 'listing_permanently_delete' ) ? true : false;

	/**
	 * Allow developers to hook into the listing removal process before actual removal.
	 *
	 * @param string|int $listing_id the id of the listing being removed.
	 * @param bool $force_delete @see https://codex.wordpress.org/Function_Reference/wp_delete_post
	 */
	do_action( 'pno_before_listing_delete', $listing_id, $force_delete );

	if ( $force_delete ) {
		wp_delete_post( $listing_id, true );
	} else {
		wp_trash_post( $listing_id );
	}

	/**
	 * Allow developers to hook into the listing removal process after the listing has already been removed from the database.
	 *
	 * @param string|int $listing_id the id of the listing removed.
	 * @param bool $force_delete @see https://codex.wordpress.org/Function_Reference/wp_delete_post
	 */
	do_action( 'pno_after_listing_delete', $listing_id, $force_delete );

}

/**
 * Get a list of listings types.
 *
 * @return array
 */
function pno_get_listings_types() {

	$types = [];

	$terms = get_terms(
		'listings-types',
		array(
			'hide_empty' => false,
			'number'     => 999,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! empty( $terms ) ) {
		foreach ( $terms as $listing_type ) {
			$types[ absint( $listing_type->term_id ) ] = esc_html( $listing_type->name );
		}
	}

	return $types;

}

/**
 * Retrieve the amount of maximum selectable categories for listings.
 *
 * @return mixed
 */
function pno_get_selectable_categories_count() {

	/**
	 * Allow developers to adjust the maximum amount of selectable categories for
	 * listings within the listing submission form.
	 *
	 * @param string $max_selection count.
	 * @return int
	 */
	$max_selection = apply_filters( 'pno_selectable_categories_count', absint( pno_get_option( 'submission_categories_amount' ) ) );
	$max_selection = is_int( $max_selection ) ? $max_selection : false;

	return $max_selection;

}

/**
 * Retrieve the amount of maximum selectable subcategories for listings.
 *
 * @return mixed
 */
function pno_get_selectable_subcategories_count() {

	/**
	 * Allow developers to adjust the maximum amount of selectable subcategories for
	 * listings within the listing submission form.
	 *
	 * @param string $max_selection count.
	 * @return int
	 */
	$max_selection = apply_filters( 'pno_selectable_subcategories_count', absint( pno_get_option( 'submission_subcategories_amount' ) ) );
	$max_selection = is_int( $max_selection ) ? $max_selection : false;

	return $max_selection;

}

/**
 * Get listing time slots options.
 *
 * @return array
 */
function pno_get_listing_time_slots() {

	$time_slots = [
		'hours'          => esc_html__( 'Enter hours' ),
		'open_all_day'   => esc_html__( 'Open all day' ),
		'closed_all_day' => esc_html__( 'Closed all day' ),
		'appointment'    => esc_html__( 'Appointment only' ),
	];

	/**
	 * Allow developers to customize the listing time slots choices.
	 *
	 * @param array $time_slots the slots currently registered.
	 * @return array the new slots.
	 */
	return apply_filters( 'pno_listing_time_slots', $time_slots );

}

/**
 * Determine if new listings submissions are moderated or not.
 *
 * @return boolean
 */
function pno_listing_submission_is_moderated() {
	return pno_get_option( 'submission_moderated', false );
}

/**
 * Determine if users can continue to edit pending listings until they are approved by an admin.
 *
 * @return boolean
 */
function pno_pending_listings_can_be_edited() {
	return pno_get_option( 'submission_allow_pendings_edit', false );
}

/**
 * Determine if published listings can be edited and if so, are they moderated or not.
 *
 * @return string
 */
function pno_published_listings_can_be_edited() {
	return pno_get_option( 'submission_edit_moderated', 'no' );
}

/**
 * Store social profiles to a given listing.
 *
 * @param string       $listing_id the id number of the listing.
 * @param array|string $social_profiles associative array of social profiles to save to the listing.
 * @return void
 */
function pno_save_listing_social_profiles( $listing_id, $social_profiles ) {

	if ( ! $social_profiles || ! $listing_id ) {
		return;
	}

	if ( ! is_array( $social_profiles ) ) {
		$social_profiles = json_decode( $social_profiles );
	}

	$profiles = [];

	if ( is_array( $social_profiles ) && ! empty( $social_profiles ) ) {
		foreach ( $social_profiles as $profile ) {
			$social_id  = sanitize_text_field( $profile->social );
			$social_url = esc_url( $profile->url );
			$profiles[] = [
				'social_id'  => $social_id,
				'social_url' => $social_url,
			];
		}
		carbon_set_post_meta( $listing_id, 'listing_social_profiles', $profiles );
	}

}

/**
 * Store opening hours within the given listing. This function is used only within
 * the frontend listing submission form.
 *
 * @param string       $listing_id the listing id number.
 * @param array|string $opening_hours the opening hours to store within the listing.
 * @return void
 */
function pno_save_submitted_listing_opening_hours( $listing_id, $opening_hours ) {

	if ( ! $opening_hours || ! $listing_id ) {
		return;
	}

	if ( ! is_array( $opening_hours ) ) {
		$opening_hours = json_decode( $opening_hours );
	}

	foreach ( $opening_hours as $day => $workday ) {
		if ( array_key_exists( $day, pno_get_days_of_the_week() ) ) {

			$slot_type  = sanitize_text_field( $workday->type );
			$slot_hours = $workday->hours;

			if ( ! empty( $slot_type ) ) {
				pno_update_listing_hours_of_operation( $listing_id, $day, $slot_type );
			}

			if ( $slot_type === 'hours' && is_array( $slot_hours ) && ! empty( $slot_hours ) ) {

				// Now grab the first set of hours.
				// These are stored in a separate custom field.
				$main_hours = $slot_hours[0];

				$opening = isset( $main_hours->opening ) && ! empty( $main_hours->opening ) ? sanitize_text_field( $main_hours->opening ) : false;
				$closing = isset( $main_hours->closing ) && ! empty( $main_hours->closing ) ? sanitize_text_field( $main_hours->closing ) : false;

				if ( $opening ) {
					pno_update_listing_opening_hours_by_day( $listing_id, $day, 'opening', $opening );
				}
				if ( $closing ) {
					pno_update_listing_opening_hours_by_day( $listing_id, $day, 'closing', $closing );
				}

				// Now remove the first set from the hours list because the first set is
				// saved into a separate custom field.
				unset( $slot_hours[0] );

				// Now store any other available timeslot into the carbon field's complex field.
				if ( is_array( $slot_hours ) && ! empty( $slot_hours ) ) {

					$additional_times = [];

					foreach ( $slot_hours as $other_hours ) {
						$other_opening      = isset( $other_hours->opening ) && ! empty( $other_hours->opening ) ? sanitize_text_field( $other_hours->opening ) : false;
						$other_closing      = isset( $other_hours->closing ) && ! empty( $other_hours->closing ) ? sanitize_text_field( $other_hours->closing ) : false;
						$additional_times[] = [
							'opening' => $other_opening,
							'closing' => $other_closing,
						];
					}

					if ( ! empty( $additional_times ) ) {
						pno_update_listing_additional_opening_hours_by_day( $listing_id, $day, $additional_times );
					}
				}
			}
		}
	}

}

/**
 * Retrieve the ID number of the author of a listing given an listing id.
 *
 * @param string|int $listing_id the listing id we're going to verify.
 * @return string|int|boolean
 */
function pno_get_listing_author( $listing_id ) {
	return get_post_field( 'post_author', $listing_id ) && 'listings' === get_post_type( $listing_id );
}

/**
 * Retrieve the url of the listing editing page together with the specified listing
 * that is going to be edited.
 *
 * @param string|int $listing_id the id number of the listing that needs to be edited.
 * @return string|boolean
 */
function pno_get_listing_edit_page_url( $listing_id ) {

	if ( ! $listing_id ) {
		return;
	}

	$page_id = pno_get_listing_editing_page_id();

	return add_query_arg( [ 'listing_id' => $listing_id ], get_permalink( $page_id ) );

}

/**
 * Determine if a given user is the author of a given listing.
 *
 * @param string|int $user_id the user we're going to verify.
 * @param string|int $listing_id the listing we're going to verify.
 * @return boolean
 */
function pno_is_user_owner_of_listing( $user_id, $listing_id ) {

	if ( ! $user_id || ! $listing_id ) {
		return false;
	}

	return $user_id == pno_get_listing_author( $listing_id ) ? true : false;

}

/**
 * Determine if a listing is pending approval.
 *
 * @param string $listing_id the id of the listing to verify.
 * @return boolean
 */
function pno_is_listing_pending_approval( $listing_id ) {

	if ( ! $listing_id ) {
		return;
	}

	return wp_cache_remember(
		"listing_{$listing_id}_pending_approval",
		function () use ( $listing_id ) {
			return get_post_type( $listing_id ) == 'listings' && get_post_status( $listing_id ) == 'pending';
		}
	);

}

/**
 * Retrieve the ID number of the listing that is about to get edited.
 *
 * @return mixed
 */
function pno_get_queried_listing_editable_id() {
	return isset( $_GET['listing_id'] ) ? absint( $_GET['listing_id'] ) : false;
}

/**
 * Retrieve the list of filters that determines the order of listings results.
 *
 * @return array
 */
function pno_get_listings_results_order_filters() {

	$filters = [
		'newest' => [
			'label'    => esc_html__( 'Newest first' ),
			'priority' => 1,
		],
		'oldest' => [
			'label'    => esc_html__( 'Oldest first' ),
			'priority' => 2,
		],
		'title'  => [
			'label'    => esc_html__( 'Title' ),
			'priority' => 3,
		],
		'random' => [
			'label'    => esc_html__( 'Random' ),
			'priority' => 4,
		],
	];

	/**
	 * Filter: modify the options available for the results order filter selector.
	 *
	 * @param array $filters the currently defined filters.
	 * @return array
	 */
	$filters = apply_filters( 'pno_listings_results_order_filters', $filters );

	if ( ! empty( $filters ) ) {
		uasort( $filters, 'pno_sort_array_by_priority' );
	}

	return $filters;

}

/**
 * Retrieve the URL for a given results order filter.
 *
 * @param string $filter_id the filter for which we're going to create the unique url.
 * @return string
 */
function pno_get_listings_results_order_filter_link( $filter_id ) {

	if ( ! $filter_id ) {
		return get_pagenum_link( get_query_var( 'paged' ) );
	}

	return add_query_arg( [ 'listings-order' => esc_attr( $filter_id ) ], pno_get_full_page_url() );

}

/**
 * Determine the currently activate listings results order filter if
 * the user has enabled one through the frontend.
 *
 * @return boolean|string
 */
function pno_get_listings_results_order_active_filter() {

	$default = pno_get_option( 'listings_default_order', 'newest' );

	$all_filters = pno_get_listings_results_order_filters();

	if ( ! is_array( $all_filters ) || empty( $all_filters ) ) {
		return;
	}

	//phpcs:ignore
	$current = isset( $_GET['listings-order'] ) && ! empty( $_GET['listings-order'] ) ? sanitize_key( $_GET['listings-order'] ) : $default;

	return $current && isset( $all_filters[ $current ] ) ? $current : $default;

}

/**
 * Retrieve the layout options available for listings results.
 *
 * @return array
 */
function pno_get_listings_layout_options() {

	$options = [
		'list' => [
			'label'    => esc_html__( 'List layout' ),
			'icon'     => 'fas fa-list-ul',
			'priority' => 1,
		],
		'grid' => [
			'label'    => esc_html__( 'Grid layout' ),
			'icon'     => 'fas fa-th',
			'priority' => 2,
		],
	];

	/**
	 * Filter: determine the layout options available for listings results.
	 *
	 * @param array $options the list of options available.
	 * @return array
	 */
	$options = apply_filters( 'pno_listings_layout_options', $options );

	if ( ! empty( $options ) ) {
		uasort( $options, 'pno_sort_array_by_priority' );
	}

	return $options;

}

/**
 * Detect the currently active listings results selected layout.
 *
 * @return string|boolean
 */
function pno_get_listings_results_active_layout() {

	$default = pno_get_option( 'listings_layout', 'list' );

	return isset( $_GET['layout'] ) && ! empty( $_GET['layout'] ) && isset( pno_get_listings_layout_options()[ $_GET['layout'] ] ) ? sanitize_key( $_GET['layout'] ) : $default;

}

/**
 * Define the options available for the amount modifier dropdown within listings results pages.
 *
 * @return array
 */
function pno_get_listings_results_per_page_options() {

	$default = pno_get_option( 'listings_per_page', get_option( 'posts_per_page', '5' ) );

	$options = [ '5', '10', '15', '20' ];

	if ( $default && ! in_array( (string) $default, $options ) ) {
		$options[] = $default;
	}

	/**
	 * Filter: determines options available for the listings amount modifier for results pages.
	 *
	 * @param mixed $options the currently defined options.
	 * @return array
	 */
	$options = apply_filters( 'pno_listings_results_per_page_options', $options );

	sort( $options );

	return $options;

}

/**
 * Retrieve the URL for a given per page amount modifier option.
 *
 * @param string|int $option the number of results to display per page.
 * @return string
 */
function pno_get_listings_results_per_page_option_link( $option ) {

	if ( ! $option ) {
		return pno_get_full_page_url();
	}

	return add_query_arg( [ 'listings-per-page' => absint( $option ) ], pno_get_full_page_url() );

}

/**
 * Determine the currently active results per page amount modifier if any is selected.
 *
 * @return string
 */
function pno_get_listings_results_per_page_active_option() {

	$default = pno_get_option( 'listings_per_page', get_option( 'posts_per_page', '5' ) );

	return isset( $_GET['listings-per-page'] ) && ! empty( $_GET['listings-per-page'] ) && in_array( $_GET['listings-per-page'], pno_get_listings_results_per_page_options() ) ? absint( $_GET['listings-per-page'] ) : $default;
}

/**
 * Get the address of a listing.
 *
 * @param string|int|boolean $listing_id optional id of a listing. If false, uses the listing within the loop.
 * @return string|boolean
 */
function pno_get_listing_address( $listing_id = false ) {

	$post_id = false;
	$address = false;

	if ( $listing_id ) {
		$post_id = absint( $listing_id );
	} else {
		$post_id = get_the_id();
	}

	if ( $post_id ) {
		$address = carbon_get_post_meta( $post_id, 'listing_location' );
	}

	return $address;

}

/**
 * Get the phone number assigned to a listing.
 *
 * @param string|int|boolean $listing_id optional id of a listing. If false, uses the listing within the loop.
 * @return string|boolean
 */
function pno_get_listing_phone_number( $listing_id = false ) {

	$post_id      = false;
	$phone_number = false;

	if ( $listing_id ) {
		$post_id = absint( $listing_id );
	} else {
		$post_id = get_the_id();
	}

	if ( $post_id ) {
		$phone_number = carbon_get_post_meta( $post_id, 'listing_phone_number' );
	}

	return $phone_number;

}

/**
 * Get tags assigned to a listing.
 *
 * @param string|int|boolean $listing_id optional id of a listing. If false, uses the listing within the loop.
 * @return array|boolean
 */
function pno_get_listing_tags( $listing_id = false ) {

	$post_id = false;
	$tags    = [];

	if ( $listing_id ) {
		$post_id = absint( $listing_id );
	} else {
		$post_id = get_the_id();
	}

	if ( $post_id ) {
		$tags = wp_get_post_terms( $post_id, 'listings-tags' );
	}

	return $tags;

}

/**
 * Determine if a thumbnail placeholder image is enabled.
 *
 * @return boolean
 */
function pno_is_listing_placeholder_image_enabled() {
	return pno_get_option( 'listing_image_placeholder', false );
}

/**
 * Retrieve the placeholder image.
 *
 * @return string
 */
function pno_get_listing_placeholder_image() {

	$image  = PNO_PLUGIN_URL . '/assets/imgs/placeholder.png';
	$custom = pno_get_option( 'listing_image_placeholder_file', false );

	return $custom && ! empty( $custom ) ? $custom : $image;

}

/**
 * Retrieve the list of html selectors that belong to internal listings links.
 * These selectors are used when using the option "Open internal listings links in new tab" is enabled.
 * Each link contained within these selectors will be opened in a new tab if the browser settings allow it.
 *
 * @return array
 */
function pno_get_internal_listing_links_selectors() {

	$selectors = [
		'.pno-listing-card h4 a',
		'.listing-img-wrapper a',
	];

	/**
	 * Filter: adjust the list of selectors that belong to internal listing links.
	 * These selectors are used when using the option "Open internal listings links in new tab" is enabled.
	 *
	 * @param mixed $selectors the list of html selectors.
	 * @return array
	 */
	return apply_filters( 'pno_internal_listing_links_selectors', $selectors );

}

/**
 * Retrieve the type assigned to a listing.
 *
 * @param boolean|string $listing_id the id of the listing to inspect.
 * @return boolean|object
 */
function pno_get_listing_type( $listing_id = false ) {

	$type = wp_get_post_terms( $listing_id, 'listings-types' );

	return is_array( $type ) && isset( $type[0] ) ? $type[0] : false;

}

/**
 * Retrieve categories assigned to a listing.
 *
 * @param boolean|string $listing_id the id of the listing to inspect.
 * @return array
 */
function pno_get_listing_categories( $listing_id = false ) {

	return wp_get_post_terms( $listing_id, 'listings-categories' );

}

/**
 * Determine if listings can be featured.
 *
 * @return boolean
 */
function pno_listings_can_be_featured() {
	return (bool) pno_get_option( 'listing_can_be_featured', false );
}

/**
 * Determine if a listing has been set as featured.
 *
 * @param string $listing_id the id of the listing to verify.
 * @return boolean
 */
function pno_listing_is_featured( $listing_id ) {

	return (bool) get_post_meta( $listing_id, '_listing_is_featured', true );

}
