<?php
/**
 * Handles actions queue scheduling with WordPress.
 * Taken from WooCommerce.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * PNO Queue
 *
 * Singleton for managing the PNO queue instance.
 */
class PNO_Queue {

	/**
	 * The single instance of the queue.
	 *
	 * @var PNO_Queue_Interface|null
	 */
	protected static $instance = null;

	/**
	 * The default queue class to initialize
	 *
	 * @var string
	 */
	protected static $default_cass = 'PNO_Action_Queue';

	/**
	 * Single instance of PNO_Queue_Interface
	 *
	 * @return PNO_Queue_Interface
	 */
	final public static function instance() {

		if ( is_null( self::$instance ) ) {
			$class          = self::get_class();
			self::$instance = new $class();
			self::$instance = self::validate_instance( self::$instance );
		}
		return self::$instance;
	}

	/**
	 * Get class to instantiate
	 *
	 * And make sure 3rd party code has the chance to attach a custom queue class.
	 *
	 * @return string
	 */
	protected static function get_class() {
		if ( ! did_action( 'plugins_loaded' ) ) {
			wc_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before plugins_loaded.', 'posterno' ), '1.0.0' );
		}

		return apply_filters( 'posterno_queue_class', self::$default_cass );
	}

	/**
	 * Enforce a PNO_Queue_Interface
	 *
	 * @param PNO_Queue_Interface $instance Instance class.
	 * @return PNO_Queue_Interface
	 */
	protected static function validate_instance( $instance ) {
		if ( false === ( $instance instanceof PNO_Queue_Interface ) ) {
			$default_class = self::$default_cass;
			/* translators: %s: Default class name */
			wc_doing_it_wrong( __FUNCTION__, sprintf( __( 'The class attached to the "posterno_queue_class" does not implement the PNO_Queue_Interface interface. The default %s class will be used instead.', 'posterno' ), $default_class ), '1.0.0' );
			$instance = new $default_class();
		}

		return $instance;
	}
}
