<?php
/**
 * The class that loads the whole plugin after requirements have been met.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Posterno' ) ) :

	final class Posterno {

		/**
		 * @var Posterno The one true Posterno
		 *
		 * @since 0.1.0
		 */
		private static $instance;

		/**
		 * Posterno loader file.
		 *
		 * @since 0.1.0
		 * @var string
		 */
		private $file = '';

		/**
		 * Templates load object
		 *
		 * @var object
		 */
		public $templates;

		/**
		 * PNO Forms object.
		 *
		 * @var object
		 */
		public $forms;

		/**
		 * Main Posterno Instance.
		 *
		 * Insures that only one instance of Posterno exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 0.1.0
		 * @static
		 * @staticvar array $instance
		 *
		 * @uses Posterno::setup_constants() Setup constants.
		 * @uses Posterno::setup_files() Setup required files.
		 * @see posterno()
		 *
		 * @return object|Posterno The one true Posterno
		 */
		public static function instance( $file = '' ) {

			// Return if already instantiated.
			if ( self::is_instantiated() ) {
				return self::$instance;
			}

			// Setup the singleton
			self::setup_instance( $file );

			// Bootstrap
			self::$instance->setup_constants();
			self::$instance->setup_files();

			// Api's
			self::$instance->templates = new PNO_Templates();
			self::$instance->forms     = PNO_Forms::instance();

			// Return the instance
			return self::$instance;

		}

		/**
		 * Throw error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '0.1.0' );
		}
		/**
		 * Disable un-serializing of the class.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '0.1.0' );
		}

		/**
		 * Return whether the main loading class has been instantiated or not.
		 *
		 * @since 0.1.0
		 *
		 * @return boolean True if instantiated. False if not.
		 */
		private static function is_instantiated() {
			// Return true if instance is correct class
			if ( ! empty( self::$instance ) && ( self::$instance instanceof Posterno ) ) {
				return true;
			}
			// Return false if not instantiated correctly
			return false;
		}

		/**
		 * Setup the singleton instance
		 *
		 * @since 0.1.0
		 * @param string $file
		 */
		private static function setup_instance( $file = '' ) {
			self::$instance       = new Posterno;
			self::$instance->file = $file;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since 0.1.0
		 * @return void
		 */
		private function setup_constants() {
			// Plugin version.
			if ( ! defined( 'PNO_VERSION' ) ) {
				define( 'PNO_VERSION', '0.1.0' );
			}
			// Plugin Root File.
			if ( ! defined( 'PNO_PLUGIN_FILE' ) ) {
				define( 'PNO_PLUGIN_FILE', $this->file );
			}
			// Plugin Base Name.
			if ( ! defined( 'PNO_PLUGIN_BASE' ) ) {
				define( 'PNO_PLUGIN_BASE', plugin_basename( PNO_PLUGIN_FILE ) );
			}
			// Plugin Folder Path.
			if ( ! defined( 'PNO_PLUGIN_DIR' ) ) {
				define( 'PNO_PLUGIN_DIR', plugin_dir_path( PNO_PLUGIN_FILE ) );
			}
			// Plugin Folder URL.
			if ( ! defined( 'PNO_PLUGIN_URL' ) ) {
				define( 'PNO_PLUGIN_URL', plugin_dir_url( PNO_PLUGIN_FILE ) );
			}
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 0.1.0
		 * @return void
		 */
		private function setup_files() {
			$this->autoload();
			$this->setup_options();
			$this->setup_objects();
			$this->setup_functions();
			$this->setup_api();

			// Admin.
			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				$this->setup_admin();
			}

		}

		/**
		 * Autoload composer's required libraries.
		 *
		 * @return void
		 */
		private function autoload() {
			require PNO_PLUGIN_DIR . '/vendor/autoload.php';
		}

		/**
		 * Setup the admin panel and load all settings & options.
		 *
		 * @return void
		 */
		private function setup_options() {
			require_once PNO_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
			$GLOBALS['pno_options'] = pno_get_settings();
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 0.1.0
		 * @return void
		 */
		private function setup_functions() {
			require_once PNO_PLUGIN_DIR . 'includes/actions.php';
			require_once PNO_PLUGIN_DIR . 'includes/filters.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/scripts.php';
			require_once PNO_PLUGIN_DIR . 'includes/post-types.php';
			require_once PNO_PLUGIN_DIR . 'includes/functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/templates-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/shortcodes.php';
		}

		/**
		 * Include required files for the administration side.
		 *
		 * @access private
		 * @since 0.1.0
		 * @return void
		 */
		private function setup_admin() {

			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-actions.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-filters.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-footer.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';

		}

		/**
		 * Include required files for the rest api layer.
		 *
		 * @access private
		 * @since 0.1.0
		 * @return void
		 */
		private function setup_api() {
			require_once PNO_PLUGIN_DIR . 'includes/api/api.php';
		}

		/**
		 * Setup objects.
		 *
		 * @return void
		 */
		private function setup_objects() {

			// Templates.
			require_once PNO_PLUGIN_DIR . 'includes/class-pno-templates.php';

			// Forms.
			require_once PNO_PLUGIN_DIR . 'includes/class-pno-forms.php';

		}

	}

endif;

/**
 * The main function for that returns Posterno
 *
 * The main function responsible for returning the one true Posterno
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $pno = posterno(); ?>
 *
 * @since 0.1.0
 * @return object|Posterno The one true Posterno Instance.
 */
function posterno() {
	return Posterno::instance();
}
