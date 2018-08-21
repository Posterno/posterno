<?php
/**
 * Handles integration with the WordPress menu editor.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add a new metabox within the menu editor for the dashboard page items.
 *
 * @since 1.0.0
 * @return void
 */
function pno_dashboard_menu_metabox() {
	add_meta_box( 'add-wpum-nav-menu', esc_html__( 'Posterno' ), 'pno_dashboard_do_wp_nav_menu_metabox', 'nav-menus', 'side', 'default' );
	add_action( 'admin_print_footer_scripts', 'pno_menu_metabox_editor_scripts' );
}
add_action( 'load-nav-menus.php', 'pno_dashboard_menu_metabox' );

/**
 * Displays the content of the Posterno dashboard metaboxes into the menu editor.
 *
 * @return void
 */
function pno_dashboard_do_wp_nav_menu_metabox() {

	global $nav_menu_selected_id;

	$walker         = new PNO_Walker_Menu_Checklist( false );
	$args           = array( 'walker' => $walker );
	$tabs           = array();
	$post_type_name = 'posterno';

	$tabs['loggedin']['label']  = __( 'Logged-In' );
	$tabs['loggedin']['pages']  = pno_nav_menu_get_loggedin_pages();
	$tabs['loggedout']['label'] = __( 'Logged-Out' );
	$tabs['loggedout']['pages'] = pno_nav_menu_get_loggedout_pages();

	$removed_args = array(
		'action',
		'customlink-tab',
		'edit-menu-item',
		'menu-item',
		'page-tab',
		'_wpnonce',
	);

	$select_all_url = add_query_arg(
		array(
			$post_type_name . '-tab' => 'all',
			'selectall'              => 1,
		),
		remove_query_arg( $removed_args )
	);

	$disable_check = null;

	if ( function_exists( 'wp_nav_menu_disabled_check' ) ) {
		$disable_check = wp_nav_menu_disabled_check( $nav_menu_selected_id );
	}

	?>

	<div id="pno-menu" class="posttypediv">

		<h4><?php esc_html_e( 'Logged-In' ); ?></h4>
		<p><?php esc_html_e( 'Logged-In links are relative to the current user, and are not visible to visitors who are not logged in.' ); ?></p>

		<div id="tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-loggedin" class="tabs-panel tabs-panel-active">
			<ul id="pno-menu-checklist-loggedin" class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedin']['pages'] ), 0, (object) $args ); ?>
			</ul>
		</div>

		<h4><?php esc_html_e( 'Logged-Out' ); ?></h4>
		<p><?php esc_html_e( 'Logged-Out links are not visible to users who are logged in.' ); ?></p>

		<div id="tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-loggedout" class="tabs-panel tabs-panel-active">
			<ul id="pno-menu-checklist-loggedout" class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedout']['pages'] ), 0, (object) $args ); ?>
			</ul>
		</div>

		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php echo esc_url( $select_all_url ); ?>#pno-menu" class="select-all"><?php esc_html_e( 'Select All' ); ?></a>
			</span>
			<span class="add-to-menu">
				<input type="submit" <?php echo esc_attr( $disable_check ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-custom-menu-item" id="submit-pno-menu" />
				<span class="spinner"></span>
			</span>
		</p>

	</div>

	<?php

}

/**
 * Create fake "post" objects for Posterno's logged-in nav menu for use in the WordPress "Menus" settings page.
 *
 * @return mixed
 */
function pno_nav_menu_get_loggedin_pages() {

	$menu_items = array();

	foreach ( pno_get_dashboard_navigation_items() as $key => $nav_item ) {
		$menu_items[] = array(
			'name' => $nav_item['name'],
			'slug' => $key,
			'link' => pno_get_dashboard_navigation_item_url( $key, $nav_item ),
		);
	}

	$menu_items[] = array(
		'name' => esc_html__( 'Logout' ),
		'slug' => 'logout',
		'link' => wp_logout_url(),
	);

	$menu_items = apply_filters( 'pno_nav_menu_get_loggedin_pages', $menu_items );

	if ( count( $menu_items ) < 1 ) {
		return false;
	}

	$page_args = array();

	foreach ( $menu_items as $item ) {
		$page_args[ $item['slug'] ] = (object) array(
			'ID'             => -1,
			'post_title'     => $item['name'],
			'post_author'    => 0,
			'post_date'      => 0,
			'post_excerpt'   => $item['slug'],
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'guid'           => $item['link'],
		);
	}

	return $page_args;

}

/**
 * Create fake "post" objects for Posterno's logged-out nav menu for use in the WordPress "Menus" settings page.
 *
 * @return mixed
 */
function pno_nav_menu_get_loggedout_pages() {

	$menu_items = array();

	$menu_items[] = array(
		'name' => esc_html__( 'Login' ),
		'slug' => 'login',
		'link' => get_permalink( pno_get_login_page_id() ),
	);

	$menu_items[] = array(
		'name' => esc_html__( 'Lost password' ),
		'slug' => 'lost-password',
		'link' => get_permalink( pno_get_password_recovery_page_id() ),
	);

	$menu_items[] = array(
		'name' => esc_html__( 'Registration' ),
		'slug' => 'registration',
		'link' => get_permalink( pno_get_registration_page_id() ),
	);

	$menu_items = apply_filters( 'pno_nav_menu_get_loggedout_pages', $menu_items );

	if ( count( $menu_items ) < 1 ) {
		return false;
	}

	$page_args = array();

	foreach ( $menu_items as $item ) {
		$page_args[ $item['slug'] ] = (object) array(
			'ID'             => -1,
			'post_title'     => $item['name'],
			'post_author'    => 0,
			'post_date'      => 0,
			'post_excerpt'   => $item['slug'],
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'guid'           => $item['link'],
		);
	}

	return $page_args;

}

/**
 * Restrict various items from view if editing a posterno menu item.
 *
 * @return void
 */
function pno_menu_metabox_editor_scripts() {
	?>
	<script type="text/javascript">
	jQuery( '#menu-to-edit').on( 'click', 'a.item-edit', function() {
		var settings  = jQuery(this).closest( '.menu-item-bar' ).next( '.menu-item-settings' );
		var css_class = settings.find( '.edit-menu-item-classes' );
		if( css_class.val().match("^pno-") ) {
			css_class.attr( 'readonly', 'readonly' );
			settings.find( '.field-url' ).css( 'display', 'none' );
		}
	});
	</script>
	<?php
}
