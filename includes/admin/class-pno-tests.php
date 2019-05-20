<?php
/**
 * Handles registration of tests for the WordPress health manager tool.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Tests {

	protected $tests = [];

	protected $results = [];

	public function __construct() {
		$this->tests   = [];
		$this->results = [];
	}

	public function add_test( $callable, $name, $type = 'direct', $groups = array( 'default' ) ) {
		if ( array_key_exists( $name, $this->tests ) ) {
			return new WP_Error( __( 'Test names must be unique.' ) );
		}
		if ( ! is_callable( $callable ) ) {
			return new WP_Error( __( 'Tests must be valid PHP callables.' ) );
		}

		$this->tests[ $name ] = array(
			'name'  => $name,
			'test'  => $callable,
			'group' => $groups,
			'type'  => $type,
		);
		return true;
	}

	public function run_test( $name ) {

		if ( array_key_exists( $name, $this->tests ) ) {
			return call_user_func( $this->tests[ $name ]['test'] );
		}
		return new WP_Error( __( 'There is no test by that name: ' ) . $name );

	}

	public function run_tests() {

		foreach ( $this->tests as $test ) {
			$result          = call_user_func( $test['test'] );
			$result['group'] = $test['group'];
			$result['type']  = $test['type'];
			$this->results[] = $result;
			if ( false === $result['pass'] ) {
				$this->pass = false;
			}
		}

	}

	public static function passing_test( $name = 'Unnamed' ) {
		return array(
			'name'       => $name,
			'pass'       => true,
			'message'    => __( 'Test Passed!' ),
			'resolution' => false,
			'severity'   => false,
		);
	}

	public static function skipped_test( $name = 'Unnamed', $message = false ) {
		return array(
			'name'       => $name,
			'pass'       => 'skipped',
			'message'    => $message,
			'resolution' => false,
			'severity'   => false,
		);
	}

	public static function failing_test( $name, $message, $resolution = false, $action = false, $severity = 'critical' ) {
		return array(
			'name'       => $name,
			'pass'       => false,
			'message'    => $message,
			'resolution' => $resolution,
			'action'     => $action,
			'severity'   => $severity,
		);
	}

	public function list_tests( $type = 'all', $group = 'all' ) {
		$tests = array();
		foreach ( $this->tests as $name => $value ) {
			// Get all valid tests by group staged.
			if ( 'all' === $group || $group === $value['group'] ) {
				$tests[ $name ] = $value;
			}

			// Next filter out any that do not match the type.
			if ( 'all' !== $type && $type !== $value['type'] ) {
				unset( $tests[ $name ] );
			}
		}

		return $tests;
	}

}
