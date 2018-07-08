<?php
/**
 * Main class that handles forms loading.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class PNO_Forms {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.26.0
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.26.0
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_posted_form' ) );
	}

	/**
	 * If a form was posted, load its class so that it can be processed before display.
	 */
	public function load_posted_form() {
		if ( ! empty( $_POST['pno_form'] ) ) {
			$this->load_form_class( sanitize_title( $_POST['pno_form'] ) );
		}
	}

	/**
	 * Load a form's class
	 *
	 * @param  string $form_name
	 * @return string class name on success, false on failure.
	 */
	private function load_form_class( $form_name ) {
		if ( ! class_exists( 'PNO_Form' ) ) {
			include PNO_PLUGIN_DIR . 'includes/abstracts/abstract-pno-form.php';
		}

		// Now try to load the form_name.
		$form_class = 'PNO_Form_' . str_replace( '-', '_', $form_name );
		$form_file  = PNO_PLUGIN_DIR . '/includes/forms/class-pno-form-' . $form_name . '.php';

		if ( class_exists( $form_class ) ) {
			return call_user_func( array( $form_class, 'instance' ) );
		}

		if ( ! file_exists( $form_file ) ) {
			return false;
		}

		if ( ! class_exists( $form_class ) ) {
			include $form_file;
		}

		// Init the form.
		return call_user_func( array( $form_class, 'instance' ) );
	}

	/**
	 * Returns the form content.
	 *
	 * @param string $form_name
	 * @param array  $atts Optional passed attributes.
	 * @return string|null
	 */
	public function get_form( $form_name, $atts = array() ) {
		$form = $this->load_form_class( $form_name );
		if ( $form ) {
			ob_start();
			$form->output( $atts );
			return ob_get_clean();
		}
	}
}
