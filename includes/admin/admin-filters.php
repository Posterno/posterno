<?php
/**
 * Registers all the filters for the administration.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add new links to the plugin's action links list.
 *
 * @since 1.0.0
 * @return array
 */
function pno_add_settings_link( $links ) {
	$settings_link = '<a href="' . admin_url( 'edit.php?post_type=listings&page=posterno-settings' ) . '">' . esc_html__( 'Settings', 'posterno' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . PNO_PLUGIN_BASE, 'pno_add_settings_link' );

/**
 * Plugin row meta links
 *
 * @since 1.4
 *
 * @param array  $plugin_meta An array of the plugin's metadata.
 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
 *
 * @return array
 */
function pno_plugin_row_meta( $plugin_meta, $plugin_file ) {

	if ( PNO_PLUGIN_BASE !== $plugin_file ) {
		return $plugin_meta;
	}

	$new_meta_links = array(
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url(
				add_query_arg(
					array(
						// 'utm_source'   => 'plugins-page',
						// 'utm_medium'   => 'plugin-row',
						// 'utm_campaign' => 'admin',
					),
					'https://docs.posterno.com'
				)
			),
			__( 'Documentation', 'posterno' )
		),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( 'https://wordpress.org/support/view/plugin-reviews/posterno?filter=5#postform' ),
			__( 'Rate us', 'posterno' )
		),
	);

	return array_merge( $plugin_meta, $new_meta_links );

}
add_filter( 'plugin_row_meta', 'pno_plugin_row_meta', 10, 2 );

/**
 * Highlight all pages used by Posterno into the pages list table.
 *
 * @param array  $post_states list of states for posts.
 * @param object $post the post being verified.
 * @return array
 */
function pno_highlight_pages( $post_states, $post ) {
	$mark    = 'Posterno';
	$post_id = $post->ID;
	switch ( $post_id ) {
		case pno_get_login_page_id():
		case pno_get_registration_page_id():
		case pno_get_password_recovery_page_id():
		case pno_get_listing_submission_page_id():
		case pno_get_listing_editing_page_id():
		case pno_get_dashboard_page_id():
		case pno_get_profile_page_id():
			$post_states['pno_page'] = $mark;
			break;
	}
	return $post_states;
}
add_filter( 'display_post_states', 'pno_highlight_pages', 10, 2 );

/**
 * Prevents cancellation of default custom fields.
 *
 * @param array  $caps capabilities list.
 * @param string $cap current capability being checked.
 * @param string $user_id the user id being checked.
 * @param array  $args args list of the post being checked.
 * @return array
 */
function pno_prevent_default_fields_cancellation( $caps, $cap, $user_id, $args ) {

	if ( 'delete_post' !== $cap || empty( $args[0] ) ) {
		return $caps;
	}

	if ( in_array( get_post_type( $args[0] ), [ 'pno_users_fields' ], true ) ) {
		$field = new PNO\Field\Profile( $args[0] );
		if ( $field instanceof PNO\Field\Profile && ! $field->can_delete() ) {
			$caps[] = 'do_not_allow';
		}
	} elseif ( in_array( get_post_type( $args[0] ), [ 'pno_signup_fields' ], true ) ) {
		$field = new PNO\Field\Registration( $args[0] );
		if ( $field instanceof PNO\Field\Registration && ! $field->can_delete() ) {
			$caps[] = 'do_not_allow';
		}
	} elseif ( in_array( get_post_type( $args[0] ), [ 'pno_listings_fields' ], true ) ) {
		$is_default = carbon_get_post_meta( $args[0], 'listing_field_is_default' );
		if ( $is_default ) {
			$caps[] = 'do_not_allow';
		}
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'pno_prevent_default_fields_cancellation', 10, 4 );

/**
 * Setup the buttons for the tinymce editor for the emails editor post type.
 *
 * @param array $settings the settings defined for the editor.
 * @return array
 */
function pno_setup_tinymce_buttons_for_emails_editor( $settings ) {

	$screen = get_current_screen();

	if ( isset( $screen->id ) && $screen->id === 'pno_emails' ) {
		$settings['toolbar1'] = 'formatselect,bold,italic,underline,bullist,numlist,blockquote,hr,link,unlink,strikethrough,spellchecker,undo,redo,dfw,wp_help';
		$settings['toolbar2'] = '';
	}

	return $settings;

}
add_filter( 'tiny_mce_before_init', 'pno_setup_tinymce_buttons_for_emails_editor', 10 );

/**
 * Remove the add media button for the tinymce editor of the emails editor.
 *
 * @param array $settings the settings defined for the editor.
 * @return array
 */
function pno_remove_add_media_button_for_emails_editor( $settings ) {

	$current_screen = get_current_screen();

	// Post types for which the media buttons should be removed.
	$post_types = array( 'pno_emails' );

	// Bail out if media buttons should not be removed for the current post type.
	if ( ! $current_screen || ! in_array( $current_screen->post_type, $post_types, true ) ) {
		return $settings;
	}

	$settings['media_buttons'] = false;

	return $settings;

}
add_filter( 'wp_editor_settings', 'pno_remove_add_media_button_for_emails_editor', 10 );

/**
 * Add a new column to the post type admin list table.
 *
 * @param array $columns already registered columns.
 * @return array
 */
function pno_emails_post_type_columns( $columns ) {

	unset( $columns['date'] );

	$columns['situations'] = esc_html__( 'Situations', 'posterno' );

	return $columns;
}
add_filter( 'manage_pno_emails_posts_columns', 'pno_emails_post_type_columns' );

/**
 * Setup bulk messages updated text for post types.
 *
 * @param array  $bulk_messages the list of messages.
 * @param string $bulk_counts the count of the posts.
 * @return array
 */
function pno_post_types_bulk_updated_messages( $bulk_messages, $bulk_counts ) {

	$bulk_messages['listings'] = array(
		'updated'   => _n( '%s listing updated.', '%s listings updated.', $bulk_counts['updated'], 'posterno' ),
		'locked'    => _n( '%s listing not updated, somebody is editing it.', '%s listings not updated, somebody is editing them.', $bulk_counts['locked'], 'posterno' ),
		'deleted'   => _n( '%s listing permanently deleted.', '%s listings permanently deleted.', $bulk_counts['deleted'], 'posterno' ),
		'trashed'   => _n( '%s listing moved to the trash.', '%s listings moved to the trash.', $bulk_counts['trashed'], 'posterno' ),
		'untrashed' => _n( '%s listing restored from the trash.', '%s listings restored from the trash.', $bulk_counts['untrashed'], 'posterno' ),
	);

	$bulk_messages['pno_emails'] = array(
		'updated'   => _n( '%s email updated.', '%s emails updated.', $bulk_counts['updated'], 'posterno' ),
		'locked'    => _n( '%s email not updated, somebody is editing it.', '%s emails not updated, somebody is editing them.', $bulk_counts['locked'], 'posterno' ),
		'deleted'   => _n( '%s email permanently deleted.', '%s emails permanently deleted.', $bulk_counts['deleted'], 'posterno' ),
		'trashed'   => _n( '%s email moved to the trash.', '%s emails moved to the trash.', $bulk_counts['trashed'], 'posterno' ),
		'untrashed' => _n( '%s email restored from the trash.', '%s emails restored from the trash.', $bulk_counts['untrashed'], 'posterno' ),
	);

	return $bulk_messages;

}

add_filter( 'bulk_post_updated_messages', 'pno_post_types_bulk_updated_messages', 10, 2 );

/**
 * Setup the api key for the google maps field of Carbon fields in the admin panel.
 *
 * @return mixed
 */
function pno_set_cb_admin_gmaps_api_key() {
	return pno_get_option( 'google_maps_api_key' );
}
add_filter( 'carbon_fields_map_field_api_key', 'pno_set_cb_admin_gmaps_api_key' );

/**
 * Customize the labels displayed when updating taxonomies.
 *
 * @param array $messages list of messages.
 * @return array
 */
function pno_updated_term_messages( $messages ) {

	$messages['listings-types'] = array(
		0 => '',
		1 => __( 'Type added.', 'posterno' ),
		2 => __( 'Type deleted.', 'posterno' ),
		3 => __( 'Type updated.', 'posterno' ),
		4 => __( 'Type not added.', 'posterno' ),
		5 => __( 'Type not updated.', 'posterno' ),
		6 => __( 'Types deleted.', 'posterno' ),
	);

	$messages['listings-categories'] = array(
		0 => '',
		1 => __( 'Category added.', 'posterno' ),
		2 => __( 'Category deleted.', 'posterno' ),
		3 => __( 'Category updated.', 'posterno' ),
		4 => __( 'Category not added.', 'posterno' ),
		5 => __( 'Category not updated.', 'posterno' ),
		6 => __( 'Categories deleted.', 'posterno' ),
	);

	$messages['listings-locations'] = array(
		0 => '',
		1 => __( 'Location added.', 'posterno' ),
		2 => __( 'Location deleted.', 'posterno' ),
		3 => __( 'Location updated.', 'posterno' ),
		4 => __( 'Location not added.', 'posterno' ),
		5 => __( 'Location not updated.', 'posterno' ),
		6 => __( 'Locations deleted.', 'posterno' ),
	);

	$messages['listings-tags'] = array(
		0 => '',
		1 => __( 'Tag added.', 'posterno' ),
		2 => __( 'Tag deleted.', 'posterno' ),
		3 => __( 'Tag updated.', 'posterno' ),
		4 => __( 'Tag not added.', 'posterno' ),
		5 => __( 'Tag not updated.', 'posterno' ),
		6 => __( 'Tags deleted.', 'posterno' ),
	);

	return $messages;

}
add_filter( 'term_updated_messages', 'pno_updated_term_messages' );
