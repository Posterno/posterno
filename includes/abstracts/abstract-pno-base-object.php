<?php
/**
 * Base Core Object.
 *
 * @package     PNO
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements a base object to be extended by core objects.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Base_Object {

	/**
	 * Object constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $object Object to populate members for.
	 */
	public function __construct( $object = null ) {

		// Bail if nothing was passed.
		if ( empty( $object ) ) {
			return;
		}

		// Maybe cast to object.
		if ( ! is_object( $object ) ) {
			$object = (object) $object;
		}

		// Set class vars.
		foreach ( get_object_vars( $object ) as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Magic isset'ter for immutability.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key key to verify.
	 * @return mixed
	 */
	public function __isset( $key = '' ) {

		if ( 'ID' === $key ) {
			$key = 'id';
		}

		$method = "get_{$key}";

		if ( method_exists( $this, $method ) ) {
			return true;

		} elseif ( property_exists( $this, $key ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Magic getter for immutability.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key the key to retrieve.
	 * @return mixed
	 */
	public function __get( $key = '' ) {

		if ( 'ID' === $key ) {
			$key = 'id';
		}

		$method = "get_{$key}";

		if ( method_exists( $this, $method ) ) {
			return call_user_func( array( $this, $method ) );

		} elseif ( property_exists( $this, $key ) ) {
			return $this->{$key};
		}

		return null;
	}

	/**
	 * Converts the given object to an array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array version of the given object.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Set class variables from arguments.
	 *
	 * @since 1.0.0
	 * @param array $args vars to set.
	 */
	protected function set_vars( $args = array() ) {

		if ( empty( $args ) ) {
			return;
		}

		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}
	}
}
