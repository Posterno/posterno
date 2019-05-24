<?php
/**
 * The class that loads the whole plugin after requirements have been met.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
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
		 * Admin notices handler.
		 *
		 * @var object
		 */
		public $admin_notices;

		/**
		 * Templates load object
		 *
		 * @var object
		 */
		public $templates;

		/**
		 * Forms loader.
		 *
		 * @var object
		 */
		public $forms;

		/**
		 * The emails sender object.
		 *
		 * @var object
		 */
		public $emails;

		/**
		 * Handles integration of the schema component.
		 *
		 * @var PNO\Schema
		 */
		public $schema;

		/**
		 * Posterno Components array
		 *
		 * @var array
		 */
		public $components = array();

		/**
		 * Holds actions queue management utilities.
		 *
		 * @var object
		 */
		public $queue;

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

			// Setup the singleton.
			self::setup_instance( $file );

			// Bootstrap.
			self::$instance->setup_constants();
			self::$instance->setup_files();
			self::$instance->setup_application();

			// Boot composer's classes.
			Brain\Cortex::boot();

			// Api's.
			self::$instance->admin_notices = TDP\WP_Notice::instance();
			self::$instance->templates     = new PNO_Templates();
			self::$instance->emails        = new PNO_Emails();
			self::$instance->forms         = PNO_Forms::instance();
			self::$instance->schema        = new PNO\SchemaOrg\Component\SchemaComponent();
			self::$instance->queue         = PNO_Queue::instance();

			// Internal components init.
			self::$instance->schema->init();

			self::maybe_schedule_cron_jobs();
			register_deactivation_hook( PNO_PLUGIN_FILE, array( __CLASS__, 'unschedule_cron_jobs' ) );

			// Return the instance.
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
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'posterno' ), '0.1.0' );
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
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'posterno' ), '0.1.0' );
		}

		/**
		 * Return whether the main loading class has been instantiated or not.
		 *
		 * @since 0.1.0
		 *
		 * @return boolean True if instantiated. False if not.
		 */
		private static function is_instantiated() {
			// Return true if instance is correct class.
			if ( ! empty( self::$instance ) && ( self::$instance instanceof Posterno ) ) {
				return true;
			}
			// Return false if not instantiated correctly.
			return false;
		}

		/**
		 * Setup the singleton instance
		 *
		 * @since 0.1.0
		 * @param string $file
		 */
		private static function setup_instance( $file = '' ) {
			self::$instance       = new Posterno();
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
				define( 'PNO_VERSION', '0.9.0' );
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
			$this->setup_utilities();
			$this->setup_components();
			$this->setup_functions();
			$this->setup_objects();
			$this->setup_api();
			$this->setup_forms();

			// Admin.
			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				$this->setup_admin();
			}

			require_once PNO_PLUGIN_DIR . 'includes/install.php';

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
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-options.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/class-pno-options-panel.php';
			$GLOBALS['pno_options'] = pno_get_settings();
		}

		/**
		 * Setup utilities used by the plugin.
		 *
		 * @return void
		 */
		private function setup_utilities() {
			require_once PNO_PLUGIN_DIR . '/includes/utils/wp-cache-remember/wp-cache-remember.php';

			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-caching.php';
			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-options-panel.php';
			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-custom-field-settings.php';

			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-opening-hours.php';
			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-hours-of-operation.php';
			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-serialize-complex-field.php';
			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-listing-type.php';
			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-listing-address.php';
			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-data-compressor.php';
			require_once PNO_PLUGIN_DIR . 'includes/datastores/datastore-email-situations.php';

			require_once PNO_PLUGIN_DIR . 'includes/class-pno-cache-helper.php';
			require_once PNO_PLUGIN_DIR . 'includes/class-pno-ajax.php';
			require_once PNO_PLUGIN_DIR . 'includes/utils/terms-hierarchy-dropdown.php';

		}

		/**
		 * Load classes related to the forms.
		 *
		 * @return void
		 */
		private function setup_forms() {
			require_once PNO_PLUGIN_DIR . 'includes/class-pno-forms.php';
		}

		/**
		 * Setup the rest of the app.
		 *
		 * @return void
		 */
		public function setup_application() {
			pno_setup_components();
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
			require_once PNO_PLUGIN_DIR . 'includes/upload-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/fields-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/listings-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/listings/listings-actions.php';
			require_once PNO_PLUGIN_DIR . 'includes/listings/dashboard-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/profiles/profiles-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/profiles/profiles-actions.php';
			require_once PNO_PLUGIN_DIR . 'includes/profiles/profiles-filters.php';
			require_once PNO_PLUGIN_DIR . 'includes/templates-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/permalinks.php';
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

			require_once PNO_PLUGIN_DIR . 'includes/admin/class-pno-getting-started.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-footer.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/custom-fields/display-custom-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-privacy-export.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-privacy-erase.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-dashboard-menu-editor.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/class-pno-admin-listings-table.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/class-pno-templates-check.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/class-pno-tests.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/class-pno-health-tests.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/class-pno-debug-data.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-notices.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-actions.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/admin-filters.php';

			require_once PNO_PLUGIN_DIR . 'includes/admin/tools/class-tool-fields-cache.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/tools/class-tool-settings-export.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/tools/class-tool-settings-import.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/tools/class-tool-fields-reset.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/tools/tools.php';

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
		 * Setup all of the custom database tables.
		 *
		 * @return void
		 */
		private function setup_components() {

			// Component helpers are loaded before everything.
			require_once PNO_PLUGIN_DIR . 'includes/class-pno-exception.php';
			require_once PNO_PLUGIN_DIR . 'includes/component-functions.php';
			require_once PNO_PLUGIN_DIR . 'includes/class-component.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/class-base.php';

			// Database Resources.
			require_once PNO_PLUGIN_DIR . 'includes/database/class-column.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/class-schema.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/class-query.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/class-row.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/class-table.php';

			// Database Schemas.
			require_once PNO_PLUGIN_DIR . 'includes/database/schemas/class-profile-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/schemas/class-listing-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/schemas/class-registration-fields.php';

			// Database Objects.
			require_once PNO_PLUGIN_DIR . 'includes/database/rows/class-profile-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/rows/class-listing-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/rows/class-registration-fields.php';

			// Database Tables.
			require_once PNO_PLUGIN_DIR . 'includes/database/tables/class-profile-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/tables/class-listing-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/tables/class-registration-fields.php';

			// Database Table Query Interfaces.
			require_once PNO_PLUGIN_DIR . 'includes/database/queries/class-profile-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/queries/class-listing-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/database/queries/class-registration-fields.php';

		}

		/**
		 * Setup objects.
		 *
		 * @return void
		 */
		private function setup_objects() {

			require_once PNO_PLUGIN_DIR . 'includes/class-pno-datetime.php';

			// Templates.
			require_once PNO_PLUGIN_DIR . 'includes/class-pno-templates.php';

			// Menus.
			require_once PNO_PLUGIN_DIR . 'includes/walkers/class-pno-walker-menu-checklist.php';

			// Fields.
			require_once PNO_PLUGIN_DIR . 'includes/admin/custom-fields/settings/class-profile.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/custom-fields/settings/class-registration.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/custom-fields/settings/class-listing.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/custom-fields/integration/class-profile.php';
			require_once PNO_PLUGIN_DIR . 'includes/admin/custom-fields/integration/class-listing.php';

			// Emails.
			require_once PNO_PLUGIN_DIR . 'includes/emails/class-pno-emails-editor-settings.php';
			require_once PNO_PLUGIN_DIR . 'includes/emails/class-pno-emails.php';
			require_once PNO_PLUGIN_DIR . 'includes/emails/emails-functions.php';

			// Avatars.
			require_once PNO_PLUGIN_DIR . 'includes/class-pno-avatars.php';

			// Listings.
			require_once PNO_PLUGIN_DIR . 'includes/listings/class-pno-listings-dashboard-actions.php';
			require_once PNO_PLUGIN_DIR . 'includes/listings/class-pno-listings-terms-custom-fields.php';
			require_once PNO_PLUGIN_DIR . 'includes/listings/class-pno-listings-expiry.php';
			require_once PNO_PLUGIN_DIR . 'includes/listings/class-pno-listings-featured.php';
			require_once PNO_PLUGIN_DIR . 'includes/listings/class-pno-business-hours-set.php';
			require_once PNO_PLUGIN_DIR . 'includes/listings/class-pno-business-hours-api.php';

			// Widgets.
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-listing-location-map.php';
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-listing-video.php';
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-listing-author.php';
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-listing-contact.php';
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-listing-details.php';
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-listing-taxonomies.php';
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-recent-listings.php';
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-featured-listings.php';
			require_once PNO_PLUGIN_DIR . 'includes/widgets/widget-listing-business-hours.php';
			require_once PNO_PLUGIN_DIR . 'includes/register-widgets.php';

			// Comments.
			require_once PNO_PLUGIN_DIR . 'includes/listings/class-pno-comments-controller.php';

			// Theme Integration.
			require_once PNO_PLUGIN_DIR . 'includes/class-pno-theme-integration.php';

			// Crons.
			require_once PNO_PLUGIN_DIR . 'includes/crons/check-for-expired-listings.php';
			require_once PNO_PLUGIN_DIR . 'includes/crons/notice-listings-soon-to-expire.php';
			require_once PNO_PLUGIN_DIR . 'includes/crons/clear-expired-transients.php';

			// Queue.
			require_once PNO_PLUGIN_DIR . 'includes/queue/class-pno-queue-interface.php';
			require_once PNO_PLUGIN_DIR . 'includes/queue/class-pno-action-queue.php';
			require_once PNO_PLUGIN_DIR . 'includes/queue/class-pno-queue.php';

		}

		/**
		 * Schedule cron jobs.
		 *
		 * @return void
		 */
		public static function maybe_schedule_cron_jobs() {
			if ( ! wp_next_scheduled( 'posterno_check_for_expired_listings' ) ) {
				wp_schedule_event( time(), 'hourly', 'posterno_check_for_expired_listings' );
			}
			if ( ! wp_next_scheduled( 'posterno_email_daily_listings_expiring_notices' ) ) {
				wp_schedule_event( time(), 'daily', 'posterno_email_daily_listings_expiring_notices' );
			}
			if ( ! wp_next_scheduled( 'posterno_clear_expired_transients' ) ) {
				wp_schedule_event( time(), 'twicedaily', 'posterno_clear_expired_transients' );
			}
		}

		/**
		 * Remove scheduled cron jobs.
		 *
		 * @return void
		 */
		public static function unschedule_cron_jobs() {
			wp_clear_scheduled_hook( 'posterno_check_for_expired_listings' );
			wp_clear_scheduled_hook( 'posterno_email_daily_listings_expiring_notices' );
			wp_clear_scheduled_hook( 'posterno_clear_expired_transients' );
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
