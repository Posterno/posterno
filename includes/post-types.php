<?php
/**
 * Register the post types and all post types related settings for Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly..
defined( 'ABSPATH' ) || exit;

/**
 * Registers the post type for the listings.
 *
 * @return void
 */
function pno_setup_post_types() {

	$labels = array(
		'name'                  => _x( 'Listings', 'Post Type General Name', 'posterno' ),
		'singular_name'         => _x( 'Listing', 'Post Type Singular Name', 'posterno' ),
		'menu_name'             => __( 'Listings', 'posterno' ),
		'name_admin_bar'        => __( 'Listing', 'posterno' ),
		'archives'              => __( 'Listings Archive', 'posterno' ),
		'attributes'            => __( 'Listing attributes', 'posterno' ),
		'parent_item_colon'     => __( 'Parent listing:', 'posterno' ),
		'all_items'             => __( 'Listings', 'posterno' ),
		'add_new_item'          => __( 'Add new listing', 'posterno' ),
		'add_new'               => __( 'Add new listing', 'posterno' ),
		'new_item'              => __( 'New listing', 'posterno' ),
		'edit_item'             => __( 'Edit listing', 'posterno' ),
		'update_item'           => __( 'Update listing', 'posterno' ),
		'view_item'             => __( 'View listing', 'posterno' ),
		'view_items'            => __( 'View listings', 'posterno' ),
		'search_items'          => __( 'Search listing', 'posterno' ),
		'not_found'             => __( 'No listings found.', 'posterno' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'posterno' ),
		'featured_image'        => __( 'Featured Image', 'posterno' ),
		'set_featured_image'    => __( 'Set featured image', 'posterno' ),
		'remove_featured_image' => __( 'Remove featured image', 'posterno' ),
		'use_featured_image'    => __( 'Use as featured image', 'posterno' ),
		'insert_into_item'      => __( 'Insert into listing', 'posterno' ),
		'uploaded_to_this_item' => __( 'Uploaded to this listing', 'posterno' ),
		'items_list'            => __( 'Listings list', 'posterno' ),
		'items_list_navigation' => __( 'Listings list navigation', 'posterno' ),
		'filter_items_list'     => __( 'Filter listings list', 'posterno' ),
	);
	$args   = array(
		'label'               => __( 'Listing', 'posterno' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
		'show_in_rest'        => true,
	);
	register_post_type( 'listings', $args );

}
add_action( 'init', 'pno_setup_post_types', 0 );

/**
 * Registers a new post type to store listings custom fields.
 *
 * @return void
 */
function pno_setup_listings_custom_fields_post_type() {

	$labels = array(
		'name'                  => esc_html__( 'Listings custom fields' ),
		'singular_name'         => esc_html__( 'Listings custom field' ),
		'menu_name'             => esc_html__( 'Listings custom fields' ),
		'name_admin_bar'        => esc_html__( 'Listings custom fields' ),
		'archives'              => esc_html__( 'Listings custom fields' ),
		'attributes'            => esc_html__( 'Item Attributes' ),
		'parent_item_colon'     => esc_html__( 'Parent Item:' ),
		'all_items'             => esc_html__( 'All listings custom fields' ),
		'add_new_item'          => esc_html__( 'Add new custom field' ),
		'add_new'               => esc_html__( 'Add new custom field' ),
		'new_item'              => esc_html__( 'New custom field' ),
		'edit_item'             => esc_html__( 'Edit custom field' ),
		'update_item'           => esc_html__( 'Update custom field' ),
		'view_item'             => esc_html__( 'View custom field' ),
		'view_items'            => esc_html__( 'View custom fields' ),
		'search_items'          => esc_html__( 'Search custom fields' ),
		'not_found'             => esc_html__( 'Not found' ),
		'not_found_in_trash'    => esc_html__( 'Not found in Trash' ),
		'featured_image'        => esc_html__( 'Featured Image' ),
		'set_featured_image'    => esc_html__( 'Set featured image' ),
		'remove_featured_image' => esc_html__( 'Remove featured image' ),
		'use_featured_image'    => esc_html__( 'Use as featured image' ),
		'insert_into_item'      => esc_html__( 'Insert into item' ),
		'uploaded_to_this_item' => esc_html__( 'Uploaded to this item' ),
		'items_list'            => esc_html__( 'Items list' ),
		'items_list_navigation' => esc_html__( 'Items list navigation' ),
		'filter_items_list'     => esc_html__( 'Filter items list' ),
	);
	$args   = array(
		'label'               => esc_html__( 'Listings custom field' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'menu_position'       => 5,
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => false,
		'capability_type'     => 'page',
		'show_in_rest'        => false,
	);
	register_post_type( 'pno_listings_fields', $args );

}
add_action( 'init', 'pno_setup_listings_custom_fields_post_type', 0 );

/**
 * Registers a new post type to store user custom fields.
 *
 * @return void
 */
function pno_setup_users_custom_fields_post_type() {

	$labels = array(
		'name'                  => esc_html__( 'Profile custom fields' ),
		'singular_name'         => esc_html__( 'Profile custom field' ),
		'menu_name'             => esc_html__( 'Profile custom fields' ),
		'name_admin_bar'        => esc_html__( 'Profile custom fields' ),
		'archives'              => esc_html__( 'Profile custom fields' ),
		'attributes'            => esc_html__( 'Item Attributes' ),
		'parent_item_colon'     => esc_html__( 'Parent Item:' ),
		'all_items'             => esc_html__( 'All users custom fields' ),
		'add_new_item'          => esc_html__( 'Add new profile field' ),
		'add_new'               => esc_html__( 'Add new profile field' ),
		'new_item'              => esc_html__( 'New custom field' ),
		'edit_item'             => esc_html__( 'Edit custom field' ),
		'update_item'           => esc_html__( 'Update custom field' ),
		'view_item'             => esc_html__( 'View custom field' ),
		'view_items'            => esc_html__( 'View custom fields' ),
		'search_items'          => esc_html__( 'Search custom fields' ),
		'not_found'             => esc_html__( 'Not found' ),
		'not_found_in_trash'    => esc_html__( 'Not found in Trash' ),
		'featured_image'        => esc_html__( 'Featured Image' ),
		'set_featured_image'    => esc_html__( 'Set featured image' ),
		'remove_featured_image' => esc_html__( 'Remove featured image' ),
		'use_featured_image'    => esc_html__( 'Use as featured image' ),
		'insert_into_item'      => esc_html__( 'Insert into item' ),
		'uploaded_to_this_item' => esc_html__( 'Uploaded to this item' ),
		'items_list'            => esc_html__( 'Items list' ),
		'items_list_navigation' => esc_html__( 'Items list navigation' ),
		'filter_items_list'     => esc_html__( 'Filter items list' ),
	);
	$args   = array(
		'label'               => esc_html__( 'Users custom field' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'menu_position'       => 5,
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => false,
		'capability_type'     => 'page',
		'show_in_rest'        => false,
	);
	register_post_type( 'pno_users_fields', $args );

}
add_action( 'init', 'pno_setup_users_custom_fields_post_type', 0 );

/**
 * Registers a new post type to store registration fields.
 *
 * @return void
 */
function pno_setup_registration_fields_post_type() {

	$labels = array(
		'name'              => esc_html__( 'Registration fields' ),
		'singular_name'     => esc_html__( 'Registration field' ),
		'menu_name'         => esc_html__( 'Registration fields' ),
		'name_admin_bar'    => esc_html__( 'Registration fields' ),
		'archives'          => esc_html__( 'Registration fields' ),
		'attributes'        => esc_html__( 'Item Attributes' ),
		'parent_item_colon' => esc_html__( 'Parent Item:' ),
		'all_items'         => esc_html__( 'All registration fields' ),
		'add_new_item'      => esc_html__( 'Add new registration field' ),
		'add_new'           => esc_html__( 'Add new registration field' ),
		'new_item'          => esc_html__( 'New custom field' ),
		'edit_item'         => esc_html__( 'Edit custom field' ),
		'update_item'       => esc_html__( 'Update custom field' ),
		'view_item'         => esc_html__( 'View custom field' ),
		'view_items'        => esc_html__( 'View custom fields' ),
		'search_items'      => esc_html__( 'Search custom fields' ),
	);
	$args   = array(
		'label'               => esc_html__( 'Registration custom field' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'menu_position'       => 5,
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => false,
		'capability_type'     => 'page',
		'show_in_rest'        => false,
	);
	register_post_type( 'pno_signup_fields', $args );

}
add_action( 'init', 'pno_setup_registration_fields_post_type', 0 );

/**
 * Change default "Enter title here" input for the profile fields post type.
 *
 * @param string $title
 * @return string
 */
function pno_user_fields_change_default_title( $title ) {

	$screen = get_current_screen();

	if ( 'pno_users_fields' == $screen->post_type ) {
		$title = esc_html__( 'Enter profile field title here' );
	} elseif ( 'pno_signup_fields' == $screen->post_type ) {
		$title = esc_html__( 'Enter registration field title here' );
	}

	return $title;

}
add_filter( 'enter_title_here', 'pno_user_fields_change_default_title' );

/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @since 0.1.0
 * @param array $messages Post updated message
 * @return array $messages New post updated messages
 */
function pno_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['pno_users_fields'] = array(
		1 => esc_html__( 'Profile field updated.' ),
		4 => esc_html__( 'Profile field updated.' ),
		6 => esc_html__( 'Profile field published.' ),
		7 => esc_html__( 'Profile field saved.' ),
		8 => esc_html__( 'Profile field submitted.' ),
	);

	$messages['pno_signup_fields'] = array(
		1 => esc_html__( 'Registration field updated.' ),
		4 => esc_html__( 'Registration field updated.' ),
		6 => esc_html__( 'Registration field published.' ),
		7 => esc_html__( 'Registration field saved.' ),
		8 => esc_html__( 'Registration field submitted.' ),
	);

	return $messages;

}
add_filter( 'post_updated_messages', 'pno_updated_messages' );

/**
 * Register taxonomies for the listings post type.
 *
 * @return void
 */
function pno_register_listings_taxonomies() {

	$labels = array(
		'name'                       => esc_html__( 'Listings types' ),
		'singular_name'              => esc_html__( 'Listings type' ),
		'menu_name'                  => esc_html__( 'Types' ),
		'all_items'                  => esc_html__( 'All listings types' ),
		'new_item_name'              => esc_html__( 'New listings type' ),
		'add_new_item'               => esc_html__( 'Add new listings type' ),
		'edit_item'                  => esc_html__( 'Edit listings type' ),
		'update_item'                => esc_html__( 'Update listings type' ),
		'view_item'                  => esc_html__( 'View listings type' ),
		'separate_items_with_commas' => esc_html__( 'Separate listings type with commas' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove listings type' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used' ),
		'popular_items'              => esc_html__( 'Popular listings types' ),
		'search_items'               => esc_html__( 'Search listings types' ),
		'not_found'                  => esc_html__( 'Not Found' ),
		'no_terms'                   => esc_html__( 'No listings types' ),
		'items_list'                 => esc_html__( 'Listings types list' ),
		'items_list_navigation'      => esc_html__( 'Listings type list navigation' ),
	);
	$args   = array(
		'labels'             => $labels,
		'hierarchical'       => false,
		'public'             => true,
		'show_ui'            => true,
		'show_in_nav_menus'  => true,
		'show_tagcloud'      => false,
		'show_in_rest'       => true,
		'show_in_quick_edit' => false,
		'meta_box_cb'        => false,
	);
	register_taxonomy( 'listings-types', array( 'listings' ), $args );

	$labels = array(
		'name'                       => esc_html__( 'Listings categories' ),
		'singular_name'              => esc_html__( 'Listings category' ),
		'menu_name'                  => esc_html__( 'Categories' ),
		'all_items'                  => esc_html__( 'All listings categories' ),
		'new_item_name'              => esc_html__( 'New listings category' ),
		'add_new_item'               => esc_html__( 'Add new listings category' ),
		'edit_item'                  => esc_html__( 'Edit listings category' ),
		'update_item'                => esc_html__( 'Update listings category' ),
		'view_item'                  => esc_html__( 'View listings category' ),
		'separate_items_with_commas' => esc_html__( 'Separate listings category with commas' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove listings category' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used' ),
		'popular_items'              => esc_html__( 'Popular listings categories' ),
		'search_items'               => esc_html__( 'Search listings categories' ),
		'not_found'                  => esc_html__( 'Not Found' ),
		'no_terms'                   => esc_html__( 'No listings categories' ),
		'items_list'                 => esc_html__( 'Listings categories list' ),
		'items_list_navigation'      => esc_html__( 'Listings category list navigation' ),
	);
	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);
	register_taxonomy( 'listings-categories', array( 'listings' ), $args );

	$labels = array(
		'name'                       => esc_html__( 'Listings locations' ),
		'singular_name'              => esc_html__( 'Listings location' ),
		'menu_name'                  => esc_html__( 'Locations' ),
		'all_items'                  => esc_html__( 'All listings locations' ),
		'new_item_name'              => esc_html__( 'New listings location' ),
		'add_new_item'               => esc_html__( 'Add new listings location' ),
		'edit_item'                  => esc_html__( 'Edit listings location' ),
		'update_item'                => esc_html__( 'Update listings location' ),
		'view_item'                  => esc_html__( 'View listings location' ),
		'separate_items_with_commas' => esc_html__( 'Separate listings location with commas' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove listings location' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used' ),
		'popular_items'              => esc_html__( 'Popular listings locations' ),
		'search_items'               => esc_html__( 'Search listings locations' ),
		'not_found'                  => esc_html__( 'Not Found' ),
		'no_terms'                   => esc_html__( 'No listings locations' ),
		'items_list'                 => esc_html__( 'Listings locations list' ),
		'items_list_navigation'      => esc_html__( 'Listings location list navigation' ),
	);
	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);
	register_taxonomy( 'listings-locations', array( 'listings' ), $args );

	$labels = array(
		'name'                       => esc_html__( 'Listings tags' ),
		'singular_name'              => esc_html__( 'Listings tag' ),
		'menu_name'                  => esc_html__( 'Tags' ),
		'all_items'                  => esc_html__( 'All listings tags' ),
		'new_item_name'              => esc_html__( 'New listings tag' ),
		'add_new_item'               => esc_html__( 'Add new listings tag' ),
		'edit_item'                  => esc_html__( 'Edit listings tag' ),
		'update_item'                => esc_html__( 'Update listings tag' ),
		'view_item'                  => esc_html__( 'View listings tag' ),
		'separate_items_with_commas' => esc_html__( 'Separate listings tag with commas' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove listings tag' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used' ),
		'popular_items'              => esc_html__( 'Popular listings tags' ),
		'search_items'               => esc_html__( 'Search listings tags' ),
		'not_found'                  => esc_html__( 'Not Found' ),
		'no_terms'                   => esc_html__( 'No listings tags' ),
		'items_list'                 => esc_html__( 'Listings tags list' ),
		'items_list_navigation'      => esc_html__( 'Listings tag list navigation' ),
	);
	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);
	register_taxonomy( 'listings-tags', array( 'listings' ), $args );

}
add_action( 'init', 'pno_register_listings_taxonomies', 0 );
