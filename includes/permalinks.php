<?php
/**
 * Handles all the routing functionalities of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly..
defined( 'ABSPATH' ) || exit;

use Brain\Cortex\Route\RouteCollectionInterface;
use Brain\Cortex\Route\QueryRoute;

add_action(
	'cortex.routes',
	function( RouteCollectionInterface $routes ) {

		$dashboard_page_id = pno_get_dashboard_page_id();
		$exists            = ( 'publish' === get_post_status( $dashboard_page_id ) ) ? true : false;

		if ( $dashboard_page_id && $exists ) {

			$page_slug = esc_attr( get_post_field( 'post_name', absint( $dashboard_page_id ) ) );
			$hierarchy = pno_get_full_page_hierarchy( $dashboard_page_id );

			if ( ! empty( $hierarchy ) && is_array( $hierarchy ) ) {
				$page_slug = '';
				foreach ( array_reverse( $hierarchy ) as $page ) {
					$parent_page_slug = esc_attr( get_post_field( 'post_name', intval( $page['id'] ) ) );
					$page_slug       .= $parent_page_slug . '/';
				}
			}

			$routes->addRoute(
				new QueryRoute(
					$page_slug . '{dashboard_navigation_item:[a-zA-Z0-9_.-]+}',
					function( array $matches ) use ( $dashboard_page_id ) {
						return [
							'dashboard_navigation_item' => $matches['dashboard_navigation_item'],
							'page_id'                   => $dashboard_page_id,
						];
					}
				)
			);

			$routes->addRoute(
				new QueryRoute(
					$page_slug . '{dashboard_navigation_item:[a-zA-Z0-9_.-]+}/page/{paged:[a-zA-Z0-9_.-]+}',
					function( array $matches ) use ( $dashboard_page_id ) {
						return [
							'dashboard_navigation_item' => $matches['dashboard_navigation_item'],
							'page_id'                   => $dashboard_page_id,
							'paged'                     => $matches['paged'],
						];
					}
				)
			);

		}
	}
);

add_action(
	'cortex.routes',
	function( RouteCollectionInterface $routes ) {

		$profile_page_id = pno_get_profile_page_id();
		$exists          = ( 'publish' === get_post_status( $profile_page_id ) ) ? true : false;

		if ( $profile_page_id && $exists ) {

			$page_slug = esc_attr( get_post_field( 'post_name', absint( $profile_page_id ) ) );
			$hierarchy = pno_get_full_page_hierarchy( $profile_page_id );

			if ( ! empty( $hierarchy ) && is_array( $hierarchy ) ) {
				$page_slug = '';
				foreach ( array_reverse( $hierarchy ) as $page ) {
					$parent_page_slug = esc_attr( get_post_field( 'post_name', intval( $page['id'] ) ) );
					$page_slug       .= $parent_page_slug . '/';
				}
			}

			$routes->addRoute(
				new QueryRoute(
					$page_slug . '{profile_id:[^/]+}',
					function( array $matches ) use ( $profile_page_id ) {
						return [
							'profile_id' => urldecode( $matches['profile_id'] ),
							'page_id'    => $profile_page_id,
						];
					}
				)
			);

			$routes->addRoute(
				new QueryRoute(
					$page_slug . '{profile_id:[^/]+}/{profile_component:[a-zA-Z0-9_.-]+}',
					function( array $matches ) use ( $profile_page_id ) {
						return [
							'profile_id'        => urldecode( $matches['profile_id'] ),
							'profile_component' => $matches['profile_component'],
							'page_id'           => $profile_page_id,
						];
					}
				)
			);

			$routes->addRoute(
				new QueryRoute(
					$page_slug . '{profile_id:[^/]+}/{profile_component:[a-zA-Z0-9_.-]+}/page/{paged:[a-zA-Z0-9_.-]+}',
					function( array $matches ) use ( $profile_page_id ) {
						return [
							'profile_id'        => urldecode( $matches['profile_id'] ),
							'profile_component' => $matches['profile_component'],
							'paged'             => $matches['paged'],
							'page_id'           => $profile_page_id,
						];
					}
				)
			);

		}
	}
);
