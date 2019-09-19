<?php
/**
 * Plugin Name:     Posterno
 * Plugin URI:      https://posterno.com
 * Description:     The world’s #1 platform that helps you create any kind of listings directory. Beautifully.
 * Author:          Posterno
 * Author URI:      https://posterno.com
 * Text Domain:     posterno
 * Domain Path:     /languages
 * Version:         1.0.12
 *
 * Posterno is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Posterno is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Posterno. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Posterno
 * @author Alessandro Tesoro, Sematico LTD.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The main plugin requirements checker
 *
 * @since 0.1.0
 */
final class PN_Requirements_Check {

	/**
	 * Plugin file
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $file = '';

	/**
	 * Plugin basename
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $base = '';

	/**
	 * Requirements array
	 *
	 * @todo Extend WP_Dependencies
	 * @var array
	 */
	private $requirements = array(

		'php' => array(
			'minimum' => '5.5.0',
			'name'    => 'PHP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false,
		),

		'wp'  => array(
			'minimum' => '4.9.6',
			'name'    => 'WordPress',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false,
		),
	);

	/**
	 * Setup plugin requirements
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		$this->file = __FILE__;
		$this->base = plugin_basename( $this->file );

		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

		$this->met()
			? $this->load()
			: $this->quit();

	}

	/**
	 * Quit without loading
	 *
	 * @since 0.1.0
	 */
	private function quit() {
		add_action( 'admin_head', [ $this, 'admin_head' ] );
		add_filter( "plugin_action_links_{$this->base}", [ $this, 'plugin_row_links' ] );
		add_action( "after_plugin_row_{$this->base}", [ $this, 'plugin_row_notice' ] );
	}

	/**
	 * Load normally
	 *
	 * @since 0.1.0
	 */
	private function load() {

		// Maybe include the bundled bootstrapper.
		if ( ! class_exists( 'Posterno' ) ) {
			require_once dirname( $this->file ) . '/includes/class-posterno.php';
		}

		// Maybe hook-in the bootstrapper.
		if ( class_exists( 'Posterno' ) ) {

			require_once dirname( $this->file ) . '/vendor/prospress/action-scheduler/action-scheduler.php';

			if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				require_once dirname( $this->file ) . '/includes/utils/EDD_SL_Plugin_Updater.php';
			}

			// Bootstrap to plugins_loaded before priority 10 to make sure
			// add-ons are loaded after us.
			add_action( 'plugins_loaded', array( $this, 'bootstrap' ), 4 );

			// Register the activation hook.
			register_activation_hook( $this->file, array( $this, 'install' ) );
		}

		do_action( 'posterno_loaded' );
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @return void
	 */
	public function install() {

		// Bootstrap to include all of the necessary files.
		$this->bootstrap();

		// Network wide.
		$network_wide = ! empty( $_GET['networkwide'] )
			? (bool) $_GET['networkwide']
			: false;
		// Call the installer directly during the activation hook.
		posterno_install( $network_wide );
	}

	/**
	 * Bootstrap.
	 *
	 * @return void
	 */
	public function bootstrap() {
		Posterno::instance( $this->file );
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	private function unmet_requirements_url() {
		return '#';
	}

	/**
	 * Plugin specific text to quickly explain what's wrong.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function unmet_requirements_text() {
		esc_html_e( 'This plugin is not fully active.', 'posterno' );
	}

	/**
	 * Plugin specific text to describe a single unmet requirement.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	private function unmet_requirements_description_text() {
		return esc_html__( 'Requires %1$s (%2$s), but (%3$s) is installed.', 'posterno' );
	}

	/**
	 * Plugin specific text to describe a single missing requirement.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	private function unmet_requirements_missing_text() {
		return esc_html__( 'Requires %1$s (%2$s), but it appears to be missing.', 'posterno' );
	}

	/**
	 * Plugin specific text used to link to an external requirements page.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	private function unmet_requirements_link() {
		return esc_html__( 'Requirements', 'posterno' );
	}

	/**
	 * Plugin specific aria label text to describe the requirements link.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	private function unmet_requirements_label() {
		return esc_html__( 'Posterno Requirements', 'posterno' );
	}

	/**
	 * Plugin specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	private function unmet_requirements_name() {
		return 'pn-requirements';
	}

	/**
	 * Plugin agnostic method to output the additional plugin row
	 *
	 * @since 0.1.0
	 */
	public function plugin_row_notice() {
		?><tr class="active <?php echo esc_attr( $this->unmet_requirements_name() ); ?>-row">
		<th class="check-column">
			<span class="dashicons dashicons-warning"></span>
		</th>
		<td class="column-primary">
			<?php $this->unmet_requirements_text(); ?>
		</td>
		<td class="column-description">
			<?php $this->unmet_requirements_description(); ?>
		</td>
		</tr>
		<?php
	}

	/**
	 * Plugin agnostic method used to output all unmet requirement information
	 *
	 */
	private function unmet_requirements_description() {
		foreach ( $this->requirements as $properties ) {
			if ( empty( $properties['met'] ) ) {
				$this->unmet_requirement_description( $properties );
			}
		}
	}

	/**
	 * Plugin agnostic method to output specific unmet requirement information
	 *
	 * @param array $requirement list of requirements.
	 */
	private function unmet_requirement_description( $requirement = array() ) {
		// Requirement exists, but is out of date.
		if ( ! empty( $requirement['exists'] ) ) {
			$text = sprintf(
				$this->unmet_requirements_description_text(),
				'<strong>' . esc_html( $requirement['name'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['current'] ) . '</strong>'
			);
			// Requirement could not be found.
		} else {
			$text = sprintf(
				$this->unmet_requirements_missing_text(),
				'<strong>' . esc_html( $requirement['name'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>'
			);
		}
		// Output the description.
		echo '<p>' . $text . '</p>';
	}

	/**
	 * Plugin agnostic method to output unmet requirements styling
	 */
	public function admin_head() {

		$name = $this->unmet_requirements_name();
		?>

		<style id="<?php echo esc_attr( $name ); ?>">
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] td,
			.plugins .<?php echo esc_html( $name ); ?>-row th,
			.plugins .<?php echo esc_html( $name ); ?>-row td {
				background: #fff5f5;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th {
				box-shadow: none;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row th span {
				margin-left: 6px;
				color: #dc3232;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins .<?php echo esc_html( $name ); ?>-row th.check-column {
				border-left: 4px solid #dc3232 !important;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p {
				margin: 0;
				padding: 0;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p:not(:last-of-type) {
				margin-bottom: 8px;
			}
		</style>
		<?php
	}

	/**
	 * Plugin agnostic method to add the "Requirements" link to row actions
	 *
	 * @param array $links action links.
	 * @return array
	 */
	public function plugin_row_links( $links = array() ) {
		// Add the Requirements link.
		$links['requirements'] =
			'<a href="' . esc_url( $this->unmet_requirements_url() ) . '" aria-label="' . esc_attr( $this->unmet_requirements_label() ) . '">'
			. esc_html( $this->unmet_requirements_link() )
			. '</a>';
		// Return links with Requirements link.
		return $links;
	}

	/**
	 * Plugin specific requirements checker
	 */
	private function check() {
		// Loop through requirements.
		foreach ( $this->requirements as $dependency => $properties ) {
			// Which dependency are we checking?
			switch ( $dependency ) {
				// PHP.
				case 'php':
					$version = phpversion();
					break;
				// WP.
				case 'wp':
					$version = get_bloginfo( 'version' );
					break;
				// Unknown.
				default:
					$version = false;
					break;
			}
			// Merge to original array.
			if ( ! empty( $version ) ) {
				$this->requirements[ $dependency ] = array_merge(
					$this->requirements[ $dependency ], array(
						'current' => $version,
						'checked' => true,
						'met'     => version_compare( $version, $properties['minimum'], '>=' ),
					)
				);
			}
		}
	}

	/**
	 * Have all requirements been met?
	 *
	 * @return boolean
	 */
	public function met() {
		// Run the check.
		$this->check();
		// Default to true (any false below wins).
		$retval  = true;
		$to_meet = wp_list_pluck( $this->requirements, 'met' );
		// Look for unmet dependencies, and exit if so.
		foreach ( $to_meet as $met ) {
			if ( empty( $met ) ) {
				$retval = false;
				continue;
			}
		}
		// Return.
		return $retval;
	}

	/**
	 * Loads the textdomain for this plugin.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'posterno', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

}

new PN_Requirements_Check();
