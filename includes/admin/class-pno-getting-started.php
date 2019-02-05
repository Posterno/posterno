<?php
/**
 * Handles creation of a getting started page displayed upon plugin's first time activation.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register a getting started page and visualize it upon plugin's first time activation.
 */
class GettingStarted {

	/**
	 * Capability required to view the page.
	 *
	 * @var string
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'admin_menus' ] );
		add_action( 'admin_head', [ $this, 'admin_head' ] );
		add_action( 'admin_init', [ $this, 'welcome' ] );
	}

	/**
	 * Register the dashboard pages which are later hidden from the menu.
	 *
	 * @return void
	 */
	public function admin_menus() {

		add_dashboard_page(
			__( 'Getting started with Posterno', 'posterno' ),
			__( 'Getting started with Posterno', 'posterno' ),
			$this->minimum_capability,
			'pno-getting-started',
			array( $this, 'getting_started_screen' )
		);

	}

	/**
	 * Hide the getting started page from the menu.
	 *
	 * @return void
	 */
	public function admin_head() {

		remove_submenu_page( 'index.php', 'pno-getting-started' );

	}

	/**
	 * Register tabs for the getting started page.
	 *
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'pno-getting-started';
		?>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected === 'pno-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'pno-getting-started' ), 'index.php' ) ) ); ?>">
				<?php esc_html_e( 'Getting Started', 'posterno' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Display the content of the getting started page.
	 *
	 * @return void
	 */
	public function getting_started_screen() {

		include PNO_PLUGIN_DIR . 'includes/admin/views/getting-started.php';

	}

	/**
	 * Trigger redirection upon plugin's first time activation.
	 *
	 * @return void
	 */
	public function welcome() {

		// Bail if no activation redirect.
		if ( ! get_transient( '_pno_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( '_pno_activation_redirect' );

		// Bail if activating from network, or bulk.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		$upgrade = get_option( 'pno_version_upgraded_from' );

		if ( ! $upgrade ) {
			wp_safe_redirect( admin_url( 'index.php?page=pno-getting-started' ) );
			exit;
		}

	}

}

( new GettingStarted() )->init();
