<?php
/**
 * Handles integration with the menu editor to customize visibility of menu items.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define the settings for the menu items.
 */
class PNO_Menus {

	/**
	 * Get things started.
	 */
	public function __construct() {
		add_action( 'carbon_fields_register_fields', [ $this, 'menu_settings' ] );
		add_action( 'admin_head', [ $this, 'cssjs' ] );
		if ( ! is_admin() ) {
			add_filter( 'wp_get_nav_menu_items', [ $this, 'exclude_menu_items' ], 10, 3 );
		}
	}

	/**
	 * Register menu settings.
	 *
	 * @return void
	 */
	public function menu_settings() {
		Container::make( 'nav_menu_item', 'Menu Settings' )
			->add_fields(
				array(
					Field::make( 'select', 'link_visibility', esc_html__( 'Display to:', 'wp-user-manager' ) )
					->add_options(
						array(
							''    => esc_html__( 'Everyone', 'wp-user-manager' ),
							'in'  => esc_html__( 'Logged in users', 'wp-user-manager' ),
							'out' => esc_html__( 'Logged out users', 'wp-user-manager' ),
						)
					)
					->set_help_text( esc_html__( 'Set the visibility of this menu item.', 'wp-user-manager' ) ),
					Field::make( 'multiselect', 'link_roles', esc_html__( 'Select roles:', 'wp-user-manager' ) )
						->set_conditional_logic(
							array(
								'relation' => 'AND',
								array(
									'field'   => 'link_visibility',
									'value'   => 'in',
									'compare' => '=',
								),
							)
						)
						->add_options( $this->get_roles() )
						->set_help_text( esc_html__( 'Select the roles that should see this menu item. Leave blank for all roles.', 'wp-user-manager' ) ),
				)
			);
	}

	/**
	 * Return an array containing user roles.
	 *
	 * @return array
	 */
	private function get_roles() {

		$roles = [];

		foreach ( pno_get_roles( true, true ) as $role ) {
			$roles[ $role['value'] ] = $role['label'];
		}

		return $roles;

	}

	/**
	 * Adjust styling of the menu settings.
	 *
	 * @return void
	 */
	public function cssjs() {

		$screen = get_current_screen();

		if ( $screen->id !== 'nav-menus' ) {
			return;
		}

		?>
		<style>
			.carbon-field.carbon-checkbox {padding-left:0px !important;}
		</style>
		<?php

	}

	/**
	 * Determine if the menu item should be visible or not.
	 *
	 * @param array $items menu items.
	 * @param array $menu menu object.
	 * @param array $args optional args.
	 * @return array
	 */
	public function exclude_menu_items( $items, $menu, $args ) {

		foreach ( $items as $key => $item ) {

			$status    = carbon_get_nav_menu_item_meta( $item->ID, 'link_visibility' );
			$roles     = carbon_get_nav_menu_item_meta( $item->ID, 'link_roles' );
			$is_logout = carbon_get_nav_menu_item_meta( $item->ID, 'convert_to_logout' );
			$visible   = true;

			switch ( $status ) {
				case 'in':
					$visible = is_user_logged_in() ? true : false;
					if ( is_array( $roles ) && ! empty( $roles ) && is_user_logged_in() ) {
						// Add the admin role for admins too.
						array_push( $roles, 'administrator' );

						$user = wp_get_current_user();
						$role = (array) $user->roles;

						// phpcs:ignore
						if ( ! array_intersect( (array)$user->roles, $roles ) ) {
							$visible = false;
						}
					}
					break;
				case 'out':
					$visible = ! is_user_logged_in() ? true : false;
					break;
			}
			// Now exclude item if not visible.
			if ( ! $visible && ! $is_logout ) {
				unset( $items[ $key ] );
			}

			if ( $is_logout && ! is_user_logged_in() ) {
				unset( $items[ $key ] );
			}
		}

		return $items;

	}

}

new PNO_Menus();
