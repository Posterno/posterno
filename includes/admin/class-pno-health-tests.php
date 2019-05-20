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

class HealthTests extends Tests {

	public function __construct() {

		parent::__construct();

		$methods = get_class_methods( __CLASS__ );

		foreach ( $methods as $method ) {
			if ( false === strpos( $method, 'test__' ) ) {
				continue;
			}
			$this->add_test( array( $this, $method ), $method, 'direct' );
		}

	}

	public function test__check_permalink_setup() {

		$name = __FUNCTION__;

		global $wp_rewrite;

		if ( empty( $wp_rewrite->permalink_structure ) ) {
			$result = self::failing_test( $name, esc_html__( 'Update your permalink structure.' ), sprintf( __( '<strong>Posterno is almost ready</strong>. You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.', 'posterno' ), admin_url( 'options-permalink.php' ) ) );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

}

