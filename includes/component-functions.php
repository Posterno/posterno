<?php
/**
 * List of functions related to components registered within Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register a new PNO component.
 *
 * @since 1.0.0
 *
 * @param string $name name of the component.
 * @param array  $args arguments for the new component.
 */
function pno_register_component( $name = '', $args = array() ) {

	$name = sanitize_key( $name );

	if ( empty( $name ) || empty( $args ) ) {
		return;
	}

	$r = wp_parse_args(
		$args, array(
			'name'   => $name,
			'schema' => '\\PNO\\Database\\Schema',
			'table'  => '\\PNO\\Database\\Table',
			'query'  => '\\PNO\\Database\\Query',
			'object' => '\\PNO\\Database\\Row',
			'meta'   => false,
		)
	);

	posterno()->components[ $name ] = new PNO\Component( $r );
}

/**
 * Get a PNO Component object
 *
 * @since 1.0.0
 * @param string $name name of the component.
 * @return mixed False if not exists, PNO_Component if exists
 */
function pno_get_component( $name = '' ) {
	$name = sanitize_key( $name );

	return isset( posterno()->components[ $name ] )
		? posterno()->components[ $name ]
		: false;
}

/**
 * Get an PNO Component interface
 *
 * @since 1.0.0
 * @param string $component component name.
 * @param string $interface interface name.
 * @return mixed False if not exists, PNO_Component interface if exists
 */
function pno_get_component_interface( $component = '', $interface = '' ) {
	$c = pno_get_component( $component );

	if ( empty( $c ) ) {
		return $c;
	}

	return $c->get_interface( $interface );
}

/**
 * Setup all PNO components
 *
 * @since 1.0.0
 */
function pno_setup_components() {
	static $setup = false;

	if ( false !== $setup ) {
		return;
	}

	// Register note.
	pno_register_component(
		'profile_field', array(
			'schema' => '\\PNO\\Database\\Schema\\Profile_Fields',
			'table'  => '\\PNO\\Database\\Tables\\Profile_Fields',
			'meta'   => false,
			'query'  => '\\PNO\\Database\\Queries\\Profile_Field',
			'object' => '\\PNO\\Profile_Fields\\Profile_Field',
		)
	);

	// Set the locally static setup var.
	$setup = true;

	// Action to allow third party components to be setup.
	do_action( 'pno_setup_components' );
}

/**
 * Install all component database tables
 *
 * This function installs all database tables used by all components (including
 * third-party and add-ons that use the Component API)
 *
 * This is used by unit tests and tools.
 *
 * @since 1.0.0
 */
function pno_install_component_database_tables() {

	$components = posterno()->components;

	if ( empty( $components ) ) {
		return;
	}

	foreach ( $components as $component ) {
		$thing = $component->get_interface( 'table' );
		if ( $thing instanceof \PNO\Database\Table && ! $thing->exists() ) {
			$thing->install();
		}
		$thing = $component->get_interface( 'meta' );
		if ( $thing instanceof \PNO\Database\Table && ! $thing->exists() ) {
			$thing->install();
		}
	}
}

/**
 * Uninstall all component database tables
 *
 * This function is destructive and disastrous, so do not call it directly
 * unless you fully intend to destroy all data (including third-party add-ons
 * that use the Component API)
 *
 * This is used by unit tests and tools.
 *
 * @since 1.0.0
 */
function pno_uninstall_component_database_tables() {

	$components = posterno()->components;

	if ( empty( $components ) ) {
		return;
	}

	foreach ( $components as $component ) {
		$thing = $component->get_interface( 'table' );
		if ( $thing instanceof \PNO\Database\Table && $thing->exists() ) {
			$thing->uninstall();
		}
		$thing = $component->get_interface( 'meta' );
		if ( $thing instanceof \PNO\Database\Table && $thing->exists() ) {
			$thing->uninstall();
		}
	}
}
