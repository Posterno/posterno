<?php
/**
 * Register the post types and all post types related settings for Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
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

	$slug = defined( 'PNO_LISTINGS_SLUG' ) ? PNO_LISTINGS_SLUG : 'listing';

	if ( pno_get_option( 'listings_slug', false ) ) {
		$slug = pno_get_option( 'listings_slug' );
	}

	$rewrite = array(
		'slug'       => $slug,
		'with_front' => true,
		'pages'      => true,
		'feeds'      => true,
	);

	if ( defined( 'PNO_DISABLE_REWRITE' ) ) {
		$rewrite = false;
	}

	$archives = defined( 'PNO_ENABLE_ARCHIVE' ) && PNO_ENABLE_ARCHIVE === true ? true : false;

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

	/**
	 * Filter: determine supported components for the listings post type registration.
	 *
	 * @param array $supports the list of supported components.
	 * @return array
	 */
	$supports = apply_filters( 'pno_listings_post_type_supports', array( 'title', 'editor', 'thumbnail', 'revisions', 'author', 'comments' ) );

	$args = array(
		'label'               => __( 'Listing', 'posterno' ),
		'labels'              => $labels,
		'supports'            => $supports,
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-posterno',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => $archives,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
		'show_in_rest'        => true,
		'rewrite'             => $rewrite,
	);
	register_post_type( 'listings', $args );

	register_post_status(
		'expired',
		array(
			'label'                     => _x( 'Expired', 'post status', 'posterno' ),
			'public'                    => pno_are_expired_listings_public(),
			'protected'                 => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'post_type'                 => array( 'listings' ),
			// translators: Placeholder %s is the number of expired posts of this type.
			'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'posterno' ),
		)
	);

}
add_action( 'init', 'pno_setup_post_types', 0 );

/**
 * Registers a new post type to store listings custom fields.
 *
 * @return void
 */
function pno_setup_listings_custom_fields_post_type() {

	$labels = array(
		'name'                  => esc_html__( 'Listings custom fields', 'posterno' ),
		'singular_name'         => esc_html__( 'Listings custom field', 'posterno' ),
		'menu_name'             => esc_html__( 'Listings custom fields', 'posterno' ),
		'name_admin_bar'        => esc_html__( 'Listings custom fields', 'posterno' ),
		'archives'              => esc_html__( 'Listings custom fields', 'posterno' ),
		'attributes'            => esc_html__( 'Item Attributes', 'posterno' ),
		'parent_item_colon'     => esc_html__( 'Parent Item:', 'posterno' ),
		'all_items'             => esc_html__( 'All listings custom fields', 'posterno' ),
		'add_new_item'          => esc_html__( 'Add new custom field', 'posterno' ),
		'add_new'               => esc_html__( 'Add new custom field', 'posterno' ),
		'new_item'              => esc_html__( 'New custom field', 'posterno' ),
		'edit_item'             => esc_html__( 'Edit custom field', 'posterno' ),
		'update_item'           => esc_html__( 'Update custom field', 'posterno' ),
		'view_item'             => esc_html__( 'View custom field', 'posterno' ),
		'view_items'            => esc_html__( 'View custom fields', 'posterno' ),
		'search_items'          => esc_html__( 'Search custom fields', 'posterno' ),
		'not_found'             => esc_html__( 'Not found', 'posterno' ),
		'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'posterno' ),
		'featured_image'        => esc_html__( 'Featured Image', 'posterno' ),
		'set_featured_image'    => esc_html__( 'Set featured image', 'posterno' ),
		'remove_featured_image' => esc_html__( 'Remove featured image', 'posterno' ),
		'use_featured_image'    => esc_html__( 'Use as featured image', 'posterno' ),
		'insert_into_item'      => esc_html__( 'Insert into item', 'posterno' ),
		'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'posterno' ),
		'items_list'            => esc_html__( 'Items list', 'posterno' ),
		'items_list_navigation' => esc_html__( 'Items list navigation', 'posterno' ),
		'filter_items_list'     => esc_html__( 'Filter items list', 'posterno' ),
	);
	$args   = array(
		'label'               => esc_html__( 'Listings custom field', 'posterno' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'menu_position'       => 5,
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => false,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'query_var'           => false,
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
		'name'                  => esc_html__( 'Profile custom fields', 'posterno' ),
		'singular_name'         => esc_html__( 'Profile custom field', 'posterno' ),
		'menu_name'             => esc_html__( 'Profile custom fields', 'posterno' ),
		'name_admin_bar'        => esc_html__( 'Profile custom fields', 'posterno' ),
		'archives'              => esc_html__( 'Profile custom fields', 'posterno' ),
		'attributes'            => esc_html__( 'Item Attributes', 'posterno' ),
		'parent_item_colon'     => esc_html__( 'Parent Item:', 'posterno' ),
		'all_items'             => esc_html__( 'All users custom fields', 'posterno' ),
		'add_new_item'          => esc_html__( 'Add new profile field', 'posterno' ),
		'add_new'               => esc_html__( 'Add new profile field', 'posterno' ),
		'new_item'              => esc_html__( 'New custom field', 'posterno' ),
		'edit_item'             => esc_html__( 'Edit custom field', 'posterno' ),
		'update_item'           => esc_html__( 'Update custom field', 'posterno' ),
		'view_item'             => esc_html__( 'View custom field', 'posterno' ),
		'view_items'            => esc_html__( 'View custom fields', 'posterno' ),
		'search_items'          => esc_html__( 'Search custom fields', 'posterno' ),
		'not_found'             => esc_html__( 'Not found', 'posterno' ),
		'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'posterno' ),
		'featured_image'        => esc_html__( 'Featured Image', 'posterno' ),
		'set_featured_image'    => esc_html__( 'Set featured image', 'posterno' ),
		'remove_featured_image' => esc_html__( 'Remove featured image', 'posterno' ),
		'use_featured_image'    => esc_html__( 'Use as featured image', 'posterno' ),
		'insert_into_item'      => esc_html__( 'Insert into item', 'posterno' ),
		'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'posterno' ),
		'items_list'            => esc_html__( 'Items list', 'posterno' ),
		'items_list_navigation' => esc_html__( 'Items list navigation', 'posterno' ),
		'filter_items_list'     => esc_html__( 'Filter items list', 'posterno' ),
	);
	$args   = array(
		'label'               => esc_html__( 'Users custom field', 'posterno' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'menu_position'       => 5,
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => false,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'query_var'           => false,
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
		'name'              => esc_html__( 'Registration fields', 'posterno' ),
		'singular_name'     => esc_html__( 'Registration field', 'posterno' ),
		'menu_name'         => esc_html__( 'Registration fields', 'posterno' ),
		'name_admin_bar'    => esc_html__( 'Registration fields', 'posterno' ),
		'archives'          => esc_html__( 'Registration fields', 'posterno' ),
		'attributes'        => esc_html__( 'Item Attributes', 'posterno' ),
		'parent_item_colon' => esc_html__( 'Parent Item:', 'posterno' ),
		'all_items'         => esc_html__( 'All registration fields', 'posterno' ),
		'add_new_item'      => esc_html__( 'Add new registration field', 'posterno' ),
		'add_new'           => esc_html__( 'Add new registration field', 'posterno' ),
		'new_item'          => esc_html__( 'New custom field', 'posterno' ),
		'edit_item'         => esc_html__( 'Edit custom field', 'posterno' ),
		'update_item'       => esc_html__( 'Update custom field', 'posterno' ),
		'view_item'         => esc_html__( 'View custom field', 'posterno' ),
		'view_items'        => esc_html__( 'View custom fields', 'posterno' ),
		'search_items'      => esc_html__( 'Search custom fields', 'posterno' ),
	);
	$args   = array(
		'label'               => esc_html__( 'Registration custom field', 'posterno' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'menu_position'       => 5,
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => false,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'query_var'           => false,
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
 * @param string $title title.
 * @return string
 */
function pno_user_fields_change_default_title( $title ) {

	$screen = get_current_screen();

	if ( 'pno_users_fields' === $screen->post_type ) {
		$title = esc_html__( 'Enter profile field title here', 'posterno' );
	} elseif ( 'pno_signup_fields' === $screen->post_type ) {
		$title = esc_html__( 'Enter registration field title here', 'posterno' );
	} elseif ( 'pno_emails' === $screen->post_type ) {
		$title = esc_html__( 'Enter email subject', 'posterno' );
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
 * @param array $messages Post updated message.
 * @return array $messages New post updated messages.
 */
function pno_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['pno_users_fields'] = array(
		1 => esc_html__( 'Profile field updated.', 'posterno' ),
		4 => esc_html__( 'Profile field updated.', 'posterno' ),
		6 => esc_html__( 'Profile field published.', 'posterno' ),
		7 => esc_html__( 'Profile field saved.', 'posterno' ),
		8 => esc_html__( 'Profile field submitted.', 'posterno' ),
	);

	$messages['pno_signup_fields'] = array(
		1 => esc_html__( 'Registration field updated.', 'posterno' ),
		4 => esc_html__( 'Registration field updated.', 'posterno' ),
		6 => esc_html__( 'Registration field published.', 'posterno' ),
		7 => esc_html__( 'Registration field saved.', 'posterno' ),
		8 => esc_html__( 'Registration field submitted.', 'posterno' ),
	);

	$messages['pno_listings_fields'] = [
		1 => esc_html__( 'Listing field updated.', 'posterno' ),
		4 => esc_html__( 'Listing field updated.', 'posterno' ),
		6 => esc_html__( 'Listing field published.', 'posterno' ),
		7 => esc_html__( 'Listing field saved.', 'posterno' ),
		8 => esc_html__( 'Listing field submitted.', 'posterno' ),
	];

	$messages['pno_emails'] = [
		1 => esc_html__( 'Email updated.', 'posterno' ),
		4 => esc_html__( 'Email updated.', 'posterno' ),
		6 => esc_html__( 'Email published.', 'posterno' ),
		7 => esc_html__( 'Email saved.', 'posterno' ),
		8 => esc_html__( 'Email submitted.', 'posterno' ),
	];

	$preview_url = get_preview_post_link( $post );
	$permalink   = get_permalink( $post_ID );

	$preview_listing_link_html = sprintf(
		' <a target="_blank" href="%1$s">%2$s</a>',
		esc_url( $preview_url ),
		__( 'Preview listing', 'posterno' )
	);

	$view_listing_link_html = sprintf(
		' <a href="%1$s">%2$s</a>',
		esc_url( $permalink ),
		__( 'View listing', 'posterno' )
	);

	$messages['listings'] = [
		1 => esc_html__( 'Listing updated.', 'posterno' ) . $view_listing_link_html,
		4 => esc_html__( 'Listing updated.', 'posterno' ),
		6 => esc_html__( 'Listing published.', 'posterno' ) . $view_listing_link_html,
		7 => esc_html__( 'Listing saved.', 'posterno' ),
		8 => esc_html__( 'Listing submitted.', 'posterno' ) . $preview_listing_link_html,
	];

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
		'name'                       => esc_html__( 'Listing types', 'posterno' ),
		'singular_name'              => esc_html__( 'Listings type', 'posterno' ),
		'menu_name'                  => esc_html__( 'Types', 'posterno' ),
		'all_items'                  => esc_html__( 'All listings types', 'posterno' ),
		'new_item_name'              => esc_html__( 'New listings type', 'posterno' ),
		'add_new_item'               => esc_html__( 'Add new listings type', 'posterno' ),
		'edit_item'                  => esc_html__( 'Edit listings type', 'posterno' ),
		'update_item'                => esc_html__( 'Update listings type', 'posterno' ),
		'view_item'                  => esc_html__( 'View listings type', 'posterno' ),
		'separate_items_with_commas' => esc_html__( 'Separate listings type with commas', 'posterno' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove listings type', 'posterno' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'posterno' ),
		'popular_items'              => esc_html__( 'Popular listings types', 'posterno' ),
		'search_items'               => esc_html__( 'Search listings types', 'posterno' ),
		'not_found'                  => esc_html__( 'Not Found', 'posterno' ),
		'no_terms'                   => esc_html__( 'No listings types', 'posterno' ),
		'items_list'                 => esc_html__( 'Listings types list', 'posterno' ),
		'items_list_navigation'      => esc_html__( 'Listings type list navigation', 'posterno' ),
		'back_to_items'              => esc_html__( '&larr; Back to types', 'posterno' ),
	);

	$slug = pno_get_option( 'listings_slug', false ) ? pno_get_option( 'listings_type_slug', false ) : 'listing-type';

	/**
	 * Filter: allows modification of the rewrite rules of the listings types taxonomy.
	 *
	 * @param array $args see https://codex.wordpress.org/Function_Reference/register_taxonomy.
	 * @return array
	 */
	$rewrite = apply_filters(
		'pno_listing_type_taxonomy_rewrite',
		array(
			'slug'         => $slug,
			'with_front'   => true,
			'hierarchical' => false,
		)
	);

	$args = array(
		'labels'             => $labels,
		'hierarchical'       => false,
		'public'             => true,
		'show_ui'            => true,
		'show_in_nav_menus'  => true,
		'show_tagcloud'      => false,
		'show_in_rest'       => true,
		'show_in_quick_edit' => false,
		'meta_box_cb'        => false,
		'show_admin_column'  => false,
		'rewrite'            => $rewrite,
	);
	register_taxonomy( 'listings-types', array( 'listings' ), $args );

	$labels = array(
		'name'                       => esc_html__( 'Listing categories', 'posterno' ),
		'singular_name'              => esc_html__( 'Listings category', 'posterno' ),
		'menu_name'                  => esc_html__( 'Categories', 'posterno' ),
		'all_items'                  => esc_html__( 'All listings categories', 'posterno' ),
		'new_item_name'              => esc_html__( 'New listings category', 'posterno' ),
		'add_new_item'               => esc_html__( 'Add new listings category', 'posterno' ),
		'edit_item'                  => esc_html__( 'Edit listings category', 'posterno' ),
		'update_item'                => esc_html__( 'Update listings category', 'posterno' ),
		'view_item'                  => esc_html__( 'View listings category', 'posterno' ),
		'separate_items_with_commas' => esc_html__( 'Separate listings category with commas', 'posterno' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove listings category', 'posterno' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'posterno' ),
		'popular_items'              => esc_html__( 'Popular listings categories', 'posterno' ),
		'search_items'               => esc_html__( 'Search listings categories', 'posterno' ),
		'not_found'                  => esc_html__( 'Not Found', 'posterno' ),
		'no_terms'                   => esc_html__( 'No listings categories', 'posterno' ),
		'items_list'                 => esc_html__( 'Listings categories list', 'posterno' ),
		'items_list_navigation'      => esc_html__( 'Listings category list navigation', 'posterno' ),
		'back_to_items'              => esc_html__( '&larr; Back to categories', 'posterno' ),
	);

	$categories_slug = pno_get_option( 'listings_categories_slug', false ) ? pno_get_option( 'listings_categories_slug', false ) : 'listing-category';

	/**
	 * Filter: allows modification of the rewrite rules of the listings categories taxonomy.
	 *
	 * @param array $args see https://codex.wordpress.org/Function_Reference/register_taxonomy.
	 * @return array
	 */
	$rewrite = apply_filters(
		'pno_listing_category_taxonomy_rewrite',
		array(
			'slug'         => $categories_slug,
			'with_front'   => true,
			'hierarchical' => true,
		)
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'rewrite'           => $rewrite,
	);
	register_taxonomy( 'listings-categories', array( 'listings' ), $args );

	$labels = array(
		'name'                       => esc_html__( 'Listing locations', 'posterno' ),
		'singular_name'              => esc_html__( 'Listings location', 'posterno' ),
		'menu_name'                  => esc_html__( 'Locations', 'posterno' ),
		'all_items'                  => esc_html__( 'All listings locations', 'posterno' ),
		'new_item_name'              => esc_html__( 'New listings location', 'posterno' ),
		'add_new_item'               => esc_html__( 'Add new listings location', 'posterno' ),
		'edit_item'                  => esc_html__( 'Edit listings location', 'posterno' ),
		'update_item'                => esc_html__( 'Update listings location', 'posterno' ),
		'view_item'                  => esc_html__( 'View listings location', 'posterno' ),
		'separate_items_with_commas' => esc_html__( 'Separate listings location with commas', 'posterno' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove listings location', 'posterno' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'posterno' ),
		'popular_items'              => esc_html__( 'Popular listings locations', 'posterno' ),
		'search_items'               => esc_html__( 'Search listings locations', 'posterno' ),
		'not_found'                  => esc_html__( 'Not Found', 'posterno' ),
		'no_terms'                   => esc_html__( 'No listings locations', 'posterno' ),
		'items_list'                 => esc_html__( 'Listings locations list', 'posterno' ),
		'items_list_navigation'      => esc_html__( 'Listings location list navigation', 'posterno' ),
		'back_to_items'              => esc_html__( '&larr; Back to locations', 'posterno' ),
	);

	$locations_slug = pno_get_option( 'listings_locations_slug', false ) ? pno_get_option( 'listings_locations_slug', false ) : 'listing-location';

	/**
	 * Filter: allows modification of the rewrite rules of the listings locations taxonomy.
	 *
	 * @param array $args see https://codex.wordpress.org/Function_Reference/register_taxonomy.
	 * @return array
	 */
	$rewrite = apply_filters(
		'pno_listing_location_taxonomy_rewrite',
		array(
			'slug'         => $locations_slug,
			'with_front'   => true,
			'hierarchical' => true,
		)
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'rewrite'           => $rewrite,
	);
	register_taxonomy( 'listings-locations', array( 'listings' ), $args );

	$labels = array(
		'name'                       => esc_html__( 'Listing tags', 'posterno' ),
		'singular_name'              => esc_html__( 'Listings tag', 'posterno' ),
		'menu_name'                  => esc_html__( 'Tags', 'posterno' ),
		'all_items'                  => esc_html__( 'All listings tags', 'posterno' ),
		'new_item_name'              => esc_html__( 'New listings tag', 'posterno' ),
		'add_new_item'               => esc_html__( 'Add new listings tag', 'posterno' ),
		'edit_item'                  => esc_html__( 'Edit listings tag', 'posterno' ),
		'update_item'                => esc_html__( 'Update listings tag', 'posterno' ),
		'view_item'                  => esc_html__( 'View listings tag', 'posterno' ),
		'separate_items_with_commas' => esc_html__( 'Separate listings tag with commas', 'posterno' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove listings tag', 'posterno' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'posterno' ),
		'popular_items'              => esc_html__( 'Popular listings tags', 'posterno' ),
		'search_items'               => esc_html__( 'Search listings tags', 'posterno' ),
		'not_found'                  => esc_html__( 'Not Found', 'posterno' ),
		'no_terms'                   => esc_html__( 'No listings tags', 'posterno' ),
		'items_list'                 => esc_html__( 'Listings tags list', 'posterno' ),
		'items_list_navigation'      => esc_html__( 'Listings tag list navigation', 'posterno' ),
		'back_to_items'              => esc_html__( '&larr; Back to tags', 'posterno' ),
	);

	$tags_slug = pno_get_option( 'listings_tags_slug', false ) ? pno_get_option( 'listings_tags_slug', false ) : 'listing-tag';

	/**
	 * Filter: allows modification of the rewrite rules of the listings tags taxonomy.
	 *
	 * @param array $args see https://codex.wordpress.org/Function_Reference/register_taxonomy.
	 * @return array
	 */
	$rewrite = apply_filters(
		'pno_listing_location_taxonomy_rewrite',
		array(
			'slug'         => $tags_slug,
			'with_front'   => true,
			'hierarchical' => false,
		)
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'rewrite'           => $rewrite,
	);
	register_taxonomy( 'listings-tags', array( 'listings' ), $args );

}
add_action( 'init', 'pno_register_listings_taxonomies', 0 );

/**
 * Define the list of post statuses for listings.
 *
 * @return array
 */
function pno_get_listing_post_statuses() {

	$statuses = [
		'draft'   => _x( 'Draft', 'post status', 'posterno' ),
		'expired' => _x( 'Expired', 'post status', 'posterno' ),
		'pending' => _x( 'Pending approval', 'post status', 'posterno' ),
		'publish' => _x( 'Published', 'post status', 'posterno' ),
	];

	return apply_filters( 'pno_listing_post_statuses', $statuses );

}

/**
 * Setup the post type that holds emails for Posterno.
 *
 * @return void
 */
function pno_setup_emails_post_type() {

	$labels = array(
		'name'               => _x( 'Posterno emails', 'Post Type General Name', 'posterno' ),
		'singular_name'      => _x( 'Email', 'Post Type Singular Name', 'posterno' ),
		'menu_name'          => __( 'Emails', 'posterno' ),
		'name_admin_bar'     => __( 'Email', 'posterno' ),
		'all_items'          => __( 'All emails', 'posterno' ),
		'add_new_item'       => __( 'Add new Posterno email', 'posterno' ),
		'add_new'            => __( 'Add new email', 'posterno' ),
		'new_item'           => __( 'New Posterno email', 'posterno' ),
		'edit_item'          => __( 'Edit Posterno email', 'posterno' ),
		'update_item'        => __( 'Update Posterno email', 'posterno' ),
		'view_item'          => __( 'View email', 'posterno' ),
		'view_items'         => __( 'View emails', 'posterno' ),
		'search_items'       => __( 'Search emails', 'posterno' ),
		'not_found'          => __( 'Not found', 'posterno' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'posterno' ),
	);

	$args = array(
		'label'               => __( 'Email', 'posterno' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor' ),
		'taxonomies'          => array( 'pno-email-type' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => current_user_can( 'manage_options' ),
		'show_in_menu'        => true,
		'menu_position'       => 70,
		'menu_icon'           => 'dashicons-email-alt',
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => false,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'query_var'           => false,
		'rewrite'             => false,
		'capability_type'     => 'page',
	);
	register_post_type( 'pno_emails', $args );

}
add_action( 'init', 'pno_setup_emails_post_type', 0 );

/**
 * Register custom taxonomy for the posterno emails.
 *
 * @return void
 */
function pno_register_email_taxonomy() {

	$labels = array(
		'add_new_item'          => _x( 'New Email Situation', 'email type taxonomy label', 'posterno' ),
		'all_items'             => _x( 'All Email Situations', 'email type taxonomy label', 'posterno' ),
		'edit_item'             => _x( 'Edit Email Situations', 'email type taxonomy label', 'posterno' ),
		'items_list'            => _x( 'Email list', 'email type taxonomy label', 'posterno' ),
		'items_list_navigation' => _x( 'Email list navigation', 'email type taxonomy label', 'posterno' ),
		'menu_name'             => _x( 'Situations', 'email type taxonomy label', 'posterno' ),
		'name'                  => _x( 'Situation', 'email type taxonomy name', 'posterno' ),
		'new_item_name'         => _x( 'New email situation name', 'email type taxonomy label', 'posterno' ),
		'not_found'             => _x( 'No email situations found.', 'email type taxonomy label', 'posterno' ),
		'no_terms'              => _x( 'No email situations', 'email type taxonomy label', 'posterno' ),
		'popular_items'         => _x( 'Popular Email Situation', 'email type taxonomy label', 'posterno' ),
		'search_items'          => _x( 'Search Emails', 'email type taxonomy label', 'posterno' ),
		'singular_name'         => _x( 'Email', 'email type taxonomy singular name', 'posterno' ),
		'update_item'           => _x( 'Update Email Situation', 'email type taxonomy label', 'posterno' ),
		'view_item'             => _x( 'View Email Situation', 'email type taxonomy label', 'posterno' ),
	);
	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => false,
		'show_ui'           => false,
		'show_admin_column' => false,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'rewrite'           => false,
	);
	register_taxonomy( 'pno-email-type', array( 'pno_emails' ), $args );

}
add_action( 'init', 'pno_register_email_taxonomy', 0 );

/**
 * Disable Gutenberg for listings.
 *
 * @param boolean $can_edit Whether the post type can be edited or not.
 * @param string  $post_type The post type being checked.
 * @return bool
 */
function pno_gutenberg_can_edit_post_type( $can_edit, $post_type ) {
	return 'listings' === $post_type ? false : $can_edit;
}
add_filter( 'gutenberg_can_edit_post_type', 'pno_gutenberg_can_edit_post_type', 10, 2 );
add_filter( 'use_block_editor_for_post_type', 'pno_gutenberg_can_edit_post_type', 10, 2 );
