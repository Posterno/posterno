<?php
/**
 * Component Functions
 *
 * This file includes functions for interacting with PNO components. An PNO
 * component is comprised of:
 *
 * - Database table/schema/query
 * - Object interface
 * - Optional meta-data
 *
 * Add-ons and third party plugins are welcome to register their own component
 * in exactly the same way that PNO does internally.
 *
 * @package     PNO
 * @subpackage  Functions/Components
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register a new PNO component (customer, adjustment, order, etc...)
 *
 * @since 0.1.0
 *
 * @param string $name component name.
 * @param array  $args arguments.
 */
function pno_register_component( $name = '', $args = array() ) {

	$name = sanitize_key( $name );

	if ( empty( $name ) || empty( $args ) ) {
		return;
	}

	$r = wp_parse_args(
		$args,
		array(
			'name'   => $name,
			'schema' => '\\PNO\\Database\\Schema',
			'table'  => '\\PNO\\Database\\Table',
			'query'  => '\\PNO\\Database\\Query',
			'object' => '\\PNO\\Database\\Row',
			'meta'   => false,
		)
	);

	posterno()->components[ $name ] = new PNO\Component( $r );

	do_action( 'pno_registered_component', $name, $r, $args );
}

/**
 * Get an PNO Component object
 *
 * @since 0.1.0
 * @param string $name component name.
 *
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
 * @since 0.1.0
 * @param string $component objects.
 * @param string $interface objects.
 *
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
 * @since 0.1.0
 */
function pno_setup_components() {

	static $setup = false;

	if ( false !== $setup ) {
		return;
	}

	pno_register_component(
		'profile_fields',
		array(
			'schema' => '\\PNO\\Database\\Schema\\Profile_Fields',
			'table'  => '\\PNO\\Database\\Tables\\Profile_Fields',
			'query'  => '\\PNO\\Database\\Queries\\Profile_Fields',
			'object' => '\\PNO\\Customers\\Customer_Address',
			'meta'   => false,
		)
	);

	pno_register_component(
		'listing_fields',
		array(
			'schema' => '\\PNO\\Database\\Schema\\Listing_Fields',
			'table'  => '\\PNO\\Database\\Tables\\Listing_Fields',
			'query'  => '\\PNO\\Database\\Queries\\Listing_Fields',
			'object' => '\\PNO\\Customers\\Customer_Address',
			'meta'   => false,
		)
	);

	pno_register_component(
		'registration_fields',
		array(
			'schema' => '\\PNO\\Database\\Schema\\Registration_Fields',
			'table'  => '\\PNO\\Database\\Tables\\Registration_Fields',
			'query'  => '\\PNO\\Database\\Queries\\Registration_Fields',
			'object' => '\\PNO\\Customers\\Customer_Address',
			'meta'   => false,
		)
	);

	$setup = true;

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
 * @since 0.1.0
 */
function pno_install_component_database_tables() {

	$components = posterno()->components;

	if ( empty( $components ) ) {
		return;
	}

	foreach ( $components as $component ) {

		$object = $component->get_interface( 'table' );
		if ( $object instanceof \PNO\Database\Table && ! $object->exists() ) {
			$object->install();
		}

		$meta = $component->get_interface( 'meta' );
		if ( $meta instanceof \PNO\Database\Table && ! $meta->exists() ) {
			$meta->install();
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
 * @since 0.1.0
 */
function pno_uninstall_component_database_tables() {

	$components = posterno()->components;

	if ( empty( $components ) ) {
		return;
	}

	foreach ( $components as $component ) {
		$object = $component->get_interface( 'table' );
		if ( $object instanceof \PNO\Database\Table && $object->exists() ) {
			$object->uninstall();
		}

		$meta = $component->get_interface( 'meta' );
		if ( $meta instanceof \PNO\Database\Table && $meta->exists() ) {
			$meta->uninstall();
		}
	}
}
