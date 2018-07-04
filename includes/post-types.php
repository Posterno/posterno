<?php
/**
 * Register the post types and all post types related settings for Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
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
		'name_admin_bar'        => __( 'Listings', 'posterno' ),
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
		'not_found'             => __( 'Not found', 'posterno' ),
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
