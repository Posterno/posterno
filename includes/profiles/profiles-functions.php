<?php
/**
 * List of functions used for profiles.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get the currently queried user's id for the profile page.
 *
 * @return string
 */
function pno_get_queried_user_id() {

	$queried_user   = get_query_var( 'profile_id', false );
	$permalink_base = pno_get_option( 'profile_permalink' );
	$user_id        = false;

	if ( ! empty( $queried_user ) && is_numeric( $queried_user ) ) {
		$user_id = $queried_user;
	} elseif ( ! empty( $queried_user ) && ! is_numeric( $queried_user ) && $permalink_base === 'username' ) {
		$user = get_user_by( 'login', $queried_user );
		if ( $user instanceof WP_User ) {
			$user_id = $user->data->ID;
		}
	} elseif ( empty( $queried_user ) ) {
		$user_id = get_current_user_id();
	}

	return absint( $user_id );

}

/**
 * Get the user's full name. Returns display name if no first name is found.
 *
 * @param mixed $user_id_or_object the user's to analyze.
 * @return string
 */
function pno_get_user_fullname( $user_id_or_object = false ) {

	if ( ! $user_id_or_object ) {
		return;
	}

	$user_info = $user_id_or_object instanceof WP_User ? $user_id_or_object : get_userdata( $user_id_or_object );

	if ( $user_info->first_name ) {

		if ( $user_info->last_name ) {
			return $user_info->first_name . ' ' . $user_info->last_name;
		}

		return $user_info->first_name;
	}

	return $user_info->display_name;

}

/**
 * Get the user's first name only. Returns display name if no first name is found.
 *
 * @param mixed $user_id_or_object the user's to analyze.
 * @return string
 */
function pno_get_user_first_name( $user_id_or_object = false ) {

	if ( ! $user_id_or_object ) {
		return;
	}

	$user_info = $user_id_or_object instanceof WP_User ? $user_id_or_object : get_userdata( $user_id_or_object );

	if ( $user_info->first_name ) {
		return $user_info->first_name;
	}

	return $user_info->display_name;

}

/**
 * Get the user's username by id.
 *
 * @param mixed $user_id_or_object the user's to analyze.
 * @return string
 */
function pno_get_user_username( $user_id_or_object = false ) {

	if ( ! $user_id_or_object ) {
		return;
	}

	$user_info = $user_id_or_object instanceof WP_User ? $user_id_or_object : get_userdata( $user_id_or_object );

	if ( $user_info->user_login ) {
		return $user_info->user_login;
	}

	return $user_info->display_name;

}

/**
 * Get the user's last name only. Returns display name if no first name is found.
 *
 * @param mixed $user_id_or_object the user's to analyze.
 * @return string
 */
function pno_get_user_last_name( $user_id_or_object = false ) {

	if ( ! $user_id_or_object ) {
		return;
	}

	$user_info = $user_id_or_object instanceof WP_User ? $user_id_or_object : get_userdata( $user_id_or_object );

	if ( $user_info->last_name ) {
		return $user_info->last_name;
	}

	return $user_info->display_name;

}

/**
 * Retrieve the registration of a given member.
 *
 * @param mixed $user_id_or_object the user's to analyze.
 * @return string|boolean
 */
function pno_get_user_registration_date( $user_id_or_object = false ) {

	if ( ! $user_id_or_object ) {
		return;
	}

	$user_info = $user_id_or_object instanceof WP_User ? $user_id_or_object : get_userdata( $user_id_or_object );

	$date = false;

	if ( isset( $user_info->user_registered ) ) {
		$date = date_i18n( get_option( 'date_format' ), strtotime( $user_info->user_registered ) );
	}

	return $date;

}

/**
 * Retrieve the list of available navigation items for the profile page.
 *
 * @return array
 */
function pno_get_profile_components() {

	$items = [
		'about'    => esc_html__( 'About', 'posterno' ),
		'posts'    => esc_html__( 'Posts', 'posterno' ),
		'listings' => esc_html__( 'Listings', 'posterno' ),
		'comments' => esc_html__( 'Comments', 'posterno' ),
	];

	/**
	 * Filter: adjust the list of available navigation items for the profile page.
	 *
	 * @param array $items the currently registered list of items.
	 * @return array
	 */
	return apply_filters( 'pno_profile_components', $items );

}

/**
 * Get the currently active profile component.
 *
 * @param array $components_menu list of menu items defined into the admin panel.
 * @return string
 */
function pno_get_profile_currently_active_component( $components_menu ) {

	$components        = pno_get_profile_components();
	$queried_component = get_query_var( 'profile_component', false );
	$active_component  = false;

	if ( empty( $queried_component ) ) {

		reset( $components_menu );
		$first            = $components_menu[ key( $components_menu ) ];
		$active_component = isset( $first->post_name ) ? $first->post_name : false;

	} else {
		if ( array_key_exists( $queried_component, $components ) ) {
			$active_component = $queried_component;
		}
	}

	return $active_component;

}

/**
 * Retrieve the profile url of a given member by it's user id.
 *
 * @param string|int $user_id the id number of the member.
 * @return boolean|string
 */
function pno_get_member_profile_url( $user_id ) {

	if ( ! $user_id ) {
		return;
	}

	$profile_page_id = pno_get_profile_page_id();
	$permalink_base  = pno_get_option( 'profile_permalink' );
	$url             = false;

	if ( $profile_page_id ) {
		$profile_page_url = untrailingslashit( get_permalink( $profile_page_id ) );
		if ( $permalink_base === 'username' ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user instanceof WP_User ) {
				$url = trailingslashit( $profile_page_url . '/' . $user->data->user_login );
			}
		} else {
			$url = trailingslashit( $profile_page_url . '/' . $user_id );
		}
	}

	return esc_url( $url );

}

/**
 * Get the url of a profile component for the currently queried profile.
 *
 * @param string $component the name of the component.
 * @return string
 */
function pno_get_current_profile_component_url( $component ) {

	$components = pno_get_profile_components();

	if ( ! array_key_exists( $component, $components ) ) {
		return false;
	}

	$profile_page_url = untrailingslashit( pno_get_member_profile_url( pno_get_queried_user_id() ) ) . '/' . $component;

	return trailingslashit( $profile_page_url );

}

/**
 * Get comments submitted by a member given a user id.
 *
 * @param string|int $user_id the id number of the user.
 * @return array
 */
function pno_get_member_submitted_comments( $user_id ) {

	if ( ! $user_id ) {
		return false;
	}

	/**
	 * Filter: adjust the arguments for the get_comments function when retrieving member's submitted comments.
	 *
	 * @param array $args
	 * @return array
	 */
	$args = apply_filters(
		'pno_get_member_comments',
		array(
			'user_id' => $user_id,
			'status'  => 'approve',
			'number'  => '10',
		)
	);

	$comments = get_comments( $args );

	return $comments;

}

/**
 * Retrieve a list of all profile fields ready for display on the public profile page.
 *
 * @return array
 */
function pno_get_public_profile_fields() {

	$fields = [];

	$not_needed = [
		'avatar',
		'first_name',
		'last_name',
		'description',
	];

	/**
	 * Filter: adjusts the query arguments for the profile fields.
	 *
	 * @param array $args
	 * @return array
	 */
	$args = apply_filters(
		'pno_public_profile_fields_args',
		[
			'number'                => 100,
			'user_meta_key__not_in' => $not_needed,
		]
	);

	$profile_fields = new PNO\Database\Queries\Profile_Fields( $args );

	if ( ! empty( $profile_fields ) && isset( $profile_fields->items ) && is_array( $profile_fields->items ) ) {
		foreach ( $profile_fields->items as $field ) {
			$fields[ $field->getObjectMetaKey() ] = [
				'name'     => $field->getTitle(),
				'priority' => $field->getPriority(),
				'meta_key' => $field->getObjectMetaKey(),
				'type'     => $field->getType(),
				'options'  => $field->getOptions(),
			];
		}
	}

	/**
	 * Filter: adjusts the list of public profile fields retrieved from the database.
	 *
	 * @param array $fields found fields.
	 * @return array
	 */
	$fields = apply_filters( 'pno_public_profile_fields', $fields );

	if ( ! empty( $fields ) ) {
		uasort( $fields, 'pno_sort_array_by_priority' );
	}

	return $fields;

}

/**
 * Retrieve a user's email address.
 *
 * @param string|boolean $user_id the id of the user.
 * @return string|boolean
 */
function pno_get_user_email( $user_id = false ) {

	if ( ! $user_id ) {
		return;
	}

	$user = get_userdata( $user_id );

	$email = false;

	if ( $user instanceof WP_User && isset( $user->user_email ) ) {
		$email = $user->user_email;
	}

	return $email;

}

/**
 * Retrieve a user's website.
 *
 * @param string|boolean $user_id the id of the user.
 * @return string|boolean
 */
function pno_get_user_website( $user_id = false ) {

	if ( ! $user_id ) {
		return;
	}

	$user = get_userdata( $user_id );

	$website = false;

	if ( $user instanceof WP_User && isset( $user->user_url ) ) {
		$website = $user->user_url;
	}

	return $website;

}

/**
 * Get the value of a profile field.
 *
 * @param string|int $user_id the id of the user.
 * @param string     $meta_key the key of the field to retrieve.
 * @return mixed
 */
function pno_get_profile_field_value( $user_id = false, $meta_key = false ) {

	if ( ! $user_id || ! $meta_key ) {
		return;
	}

	$value = false;

	if ( pno_is_default_field( $meta_key ) ) {

		switch ( $meta_key ) {
			case 'email':
				$value = pno_get_user_email( $user_id );
				break;
			case 'website':
				$value = pno_get_user_website( $user_id );
				break;
		}
	} else {

		$value = carbon_get_user_meta( $user_id, $meta_key );

	}

	return $value;

}
