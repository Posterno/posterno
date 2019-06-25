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

use WP_Error;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register new health tests and verify them.
 */
class Tests {

	/**
	 * List of tests.
	 *
	 * @var array
	 */
	protected $tests = [];

	/**
	 * Verification results.
	 *
	 * @var array
	 */
	protected $results = [];

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->tests   = [];
		$this->results = [];
	}

	/**
	 * Add a new test.
	 *
	 * @param callable $callable Test to add to queue.
	 * @param string   $name Unique name for the test.
	 * @param string   $type   Optional. Core Site Health type: 'direct' if test can be run during initial load or 'async' if test should run async.
	 * @param array    $groups Optional. Testing groups to add test to.
	 * @return mixed
	 */
	public function add_test( $callable, $name, $type = 'direct', $groups = array( 'default' ) ) {
		if ( array_key_exists( $name, $this->tests ) ) {
			return new WP_Error( __( 'Test names must be unique.', 'posterno' ) );
		}
		if ( ! is_callable( $callable ) ) {
			return new WP_Error( __( 'Tests must be valid PHP callables.', 'posterno' ) );
		}

		$this->tests[ $name ] = array(
			'name'  => $name,
			'test'  => $callable,
			'group' => $groups,
			'type'  => $type,
		);
		return true;
	}

	/**
	 * Run a specific test.
	 *
	 * @param string $name Name of test.
	 *
	 * @return mixed $result Test result array or WP_Error if invalid name. {
	 * @type string $name Test name
	 * @type mixed  $pass True if passed, false if failed, 'skipped' if skipped.
	 * @type string $message Human-readable test result message.
	 * @type string $resolution Human-readable resolution steps.
	 * }
	 */
	public function run_test( $name ) {

		if ( array_key_exists( $name, $this->tests ) ) {
			return call_user_func( $this->tests[ $name ]['test'] );
		}
		return new WP_Error( __( 'There is no test by that name: ', 'posterno' ) . $name );

	}

	/**
	 * Run all tests.
	 *
	 * @return void
	 */
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

	/**
	 * Mark a test as passing.
	 *
	 * @param string $name name of the test.
	 * @return array
	 */
	public static function passing_test( $name = 'Unnamed' ) {
		return array(
			'name'       => $name,
			'pass'       => true,
			'message'    => __( 'Test Passed!', 'posterno' ),
			'resolution' => false,
			'severity'   => false,
		);
	}

	/**
	 * Mark a test as skipped.
	 *
	 * @param string  $name name of the test.
	 * @param boolean $message message.
	 * @return array
	 */
	public static function skipped_test( $name = 'Unnamed', $message = false ) {
		return array(
			'name'       => $name,
			'pass'       => 'skipped',
			'message'    => $message,
			'resolution' => false,
			'severity'   => false,
		);
	}

	/**
	 * Mark a test as failed.
	 *
	 * @param string $name Test name.
	 * @param string $message Message detailing the failure.
	 * @param string $resolution Optional. Steps to resolve.
	 * @param string $action Optional. URL to direct users to self-resolve.
	 * @param string $severity Optional. "critical" or "recommended" for failure stats. "good" for passing.
	 *
	 * @return array Test results.
	 */
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

	/**
	 * Get list of all available tests.
	 *
	 * @param string $type the type of tests to get.
	 * @param string $group from which group.
	 * @return array
	 */
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
