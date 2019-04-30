<?php
/**
 * Base class that handles display and loading of all posterno's forms.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base class for all forms.
 */
class PNO_Forms {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
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
			$this->load_form_class( ucfirst( sanitize_title( $_POST['pno_form'] ) ) );
		}
	}

	/**
	 * Load a form's class
	 *
	 * @param  string $form_name name of the form to load.
	 * @return string class name on success, false on failure.
	 */
	private function load_form_class( $form_name ) {

		// Now try to load the form_name.
		$form_class = '\\PNO\\Forms\\' . ucfirst( $form_name );
		$form_file  = PNO_PLUGIN_DIR . 'includes/forms/' . ucfirst( $form_name ) . '.php';

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
	 * @param string $form_name name of the form to retrive.
	 * @param array  $atts Optional passed attributes.
	 * @return string|null
	 */
	public function get_form( $form_name, $atts = array() ) {
		$form = $this->load_form_class( $form_name );
		if ( $form ) {
			ob_start();
			$form->render( $atts );
			return ob_get_clean();
		}
	}
}
