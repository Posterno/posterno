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

/**
 * Handle all core tests of the plugin.
 */
class HealthTests extends Tests {

	/**
	 * Get things started.
	 */
	public function __construct() {

		parent::__construct();

		$methods = get_class_methods( __CLASS__ );

		foreach ( $methods as $method ) {
			if ( false === strpos( $method, 'test__' ) ) {
				continue;
			}
			$this->add_test( array( $this, $method ), $method, 'direct' );
		}

		$addons = apply_filters( 'pno_registered_premium_addons', [] );

		if ( ! empty( $addons ) ) {
			foreach ( $addons as $addon ) {
				$this->add_test( array( $this, 'addon_test' ), 'addon_test', 'direct', [ 'default' ], $addon );
			}
		}

	}

	/**
	 * Verify the license for addons is valid.
	 *
	 * @param array $addon the addon definition.
	 * @return array
	 */
	public function addon_test( $addon ) {

		$name = isset( $addon['name'] ) ? $addon['name'] : false;
		$data = isset( $addon['data'] ) ? $addon['data'] : false;

		if ( ! $name ) {
			return;
		}

		if ( ! isset( $data->license ) || ( isset( $data->license ) && $data->license !== 'valid' ) ) {
			$result = self::failing_test( $name, sprintf( __( 'Cannot validate license for the "%s" addon.', 'posterno' ), $name ), sprintf( __( 'The license is either missing, invalid or expired. <a href="%s">Please validate your license</a> so that you are not missing out on updates and support.', 'posterno' ), admin_url( 'tools.php?page=posterno-tools&tab=licenses' ) ) );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify permalinks are properly setup.
	 *
	 * @return array
	 */
	public function test__verify_permalinks_have_been_properly_setup() {

		$name = __FUNCTION__;

		global $wp_rewrite;

		if ( empty( $wp_rewrite->permalink_structure ) ) {
			$result = self::failing_test( $name, esc_html__( 'Update your permalink structure.', 'posterno' ), sprintf( __( '<strong>Posterno is almost ready</strong>. You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.', 'posterno' ), admin_url( 'options-permalink.php' ) ) );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify the login page has been created.
	 *
	 * @return array
	 */
	public function test__login_page_has_been_created() {

		$name = __FUNCTION__;

		if ( ! pno_get_login_page_id() ) {
			$result = self::failing_test( $name, esc_html__( 'A login page has not been created.', 'posterno' ), sprintf( __( 'You must <a href="%s" target="_blank" rel="nofollow">setup a login page.</a>', 'posterno' ), 'https://docs.posterno.com/article/460-login-page-setup' ) );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify the required page has been created.
	 *
	 * @return array
	 */
	public function test__password_recovery_page_has_been_created() {

		$name = __FUNCTION__;

		if ( ! pno_get_password_recovery_page_id() ) {
			$result = self::failing_test( $name, esc_html__( 'A password recovery page has not been created.', 'posterno' ), sprintf( __( 'You must <a href="%s" target="_blank" rel="nofollow">setup a password recovery page.</a>', 'posterno' ), 'https://docs.posterno.com/article/461-password-recovery-page-setup' ) );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify the required page has been created.
	 *
	 * @return array
	 */
	public function test__registration_page_has_been_created() {

		$name = __FUNCTION__;

		if ( ! pno_get_registration_page_id() ) {
			$result = self::failing_test( $name, esc_html__( 'A registration page has not been created.', 'posterno' ), sprintf( __( 'You must <a href="%s" target="_blank" rel="nofollow">setup a registration page.</a>', 'posterno' ), 'https://docs.posterno.com/article/462-registration-page-setup' ) );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify the required page has been created.
	 *
	 * @return array
	 */
	public function test__dashboard_page_has_been_created() {

		$name = __FUNCTION__;

		if ( ! pno_get_dashboard_page_id() ) {
			$result = self::failing_test( $name, esc_html__( 'A dashboard page has not been created.', 'posterno' ), sprintf( __( 'You must <a href="%s" target="_blank" rel="nofollow">setup a dashboard page.</a>', 'posterno' ), 'https://docs.posterno.com/article/463-dashboard-page-setup' ) );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify the required page has been created.
	 *
	 * @return array
	 */
	public function test__listing_submission_page_has_been_created() {

		$name = __FUNCTION__;

		if ( ! pno_get_listing_submission_page_id() ) {
			$result = self::failing_test( $name, esc_html__( 'A listing submission page has not been created.', 'posterno' ), sprintf( __( 'You should <a href="%s" target="_blank" rel="nofollow">setup a listing submission page,</a> if you wish your members to submit listings from the frontend.', 'posterno' ), 'https://docs.posterno.com/article/464-listing-submission-page-setup' ), false, 'recommended' );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify the required page has been created.
	 *
	 * @return array
	 */
	public function test__listing_editing_page_has_been_created() {

		$name = __FUNCTION__;

		if ( ! pno_get_listing_editing_page_id() ) {
			$result = self::failing_test( $name, esc_html__( 'A listing editing page has not been created.', 'posterno' ), sprintf( __( 'You should <a href="%s" target="_blank" rel="nofollow">setup a listing editing page,</a> if you wish your members to edit listings from the frontend.', 'posterno' ), 'https://docs.posterno.com/article/465-listing-editing-page-setup' ), false, 'recommended' );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify the required page has been created.
	 *
	 * @return array
	 */
	public function test__public_profile_page_has_been_created() {

		$name = __FUNCTION__;

		if ( ! pno_get_profile_page_id() ) {
			$result = self::failing_test( $name, esc_html__( 'A public profile page has not been created.', 'posterno' ), sprintf( __( 'You should <a href="%s" target="_blank" rel="nofollow">setup a public profile page</a>, If you wish your members to view profiles of other members on your site.', 'posterno' ), 'https://docs.posterno.com/article/466-public-profile-page-setup' ), false, 'recommended' );
		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Verify that all custom database tables from components are installed.
	 *
	 * @return array
	 */
	public function test__default_database_tables_are_installed() {

		$name = __FUNCTION__;

		$components = posterno()->components;

		foreach ( $components as $component ) {

			$object = $component->get_interface( 'table' );

			if ( $object instanceof \PNO\Database\Table && ! $object->exists() ) {
				return self::failing_test( $name, esc_html__( 'Some default database tables are missing.', 'posterno' ), esc_html__( 'Please contact support.', 'posterno' ) );
			} elseif ( $object instanceof \PNO\Database\Table && $object->exists() ) {
				$result = self::passing_test( $name );
			}
		}

		return $result;

	}

	/**
	 * Verify that components tables are not empty.
	 *
	 * @return array
	 */
	public function test__default_data_has_been_installed() {

		$name = __FUNCTION__;

		$registration = new \PNO\Database\Queries\Registration_Fields( [ 'number' => 1 ] );

		if ( ! isset( $registration->items ) || isset( $registration->items ) && empty( $registration->items ) ) {
			return self::failing_test( $name, esc_html__( 'Some default data is missing.', 'posterno' ), esc_html__( 'Please contact support.', 'posterno' ) );
		}

		$listings = new \PNO\Database\Queries\Listing_Fields( [ 'number' => 1 ] );

		if ( ! isset( $listings->items ) || isset( $listings->items ) && empty( $listings->items ) ) {
			return self::failing_test( $name, esc_html__( 'Some default data is missing.', 'posterno' ), esc_html__( 'Please contact support.', 'posterno' ) );
		}

		$profile = new \PNO\Database\Queries\Profile_Fields( [ 'number' => 1 ] );

		if ( ! isset( $profile->items ) || isset( $profile->items ) && empty( $profile->items ) ) {
			return self::failing_test( $name, esc_html__( 'Some default data is missing.', 'posterno' ), esc_html__( 'Please contact support.', 'posterno' ) );
		}

		$result = self::passing_test( $name );

		return $result;

	}

	/**
	 * Test outdated template files.
	 *
	 * @return array
	 */
	public function test__verify_outdated_posterno_template_files_in_theme() {

		$name = __FUNCTION__;

		if ( \PNO\Admin\TemplatesCheck::theme_has_outdated_templates() ) {

			$debug  = $this->get_outdated_template_files_table_list();
			$result = self::failing_test( $name, esc_html__( 'Your theme contains outdated copies of some Posterno template files.', 'posterno' ), $debug );

		} else {
			$result = self::passing_test( $name );
		}

		return $result;

	}

	/**
	 * Get markup list of outdated template files.
	 *
	 * @return string
	 */
	private function get_outdated_template_files_table_list() {

		$files = \PNO\Admin\TemplatesCheck::get_outdated_template_files();

		ob_start();

		?>
		<table class="widefat striped health-check-table" role="presentation">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Outdated template files:', 'posterno' ); ?></td>
					<td></td>
				</tr>

				<?php foreach ( $files as $file ) : ?>

				<tr>
					<td style="width:50%;">
						<code style="font-size:11px;"><?php echo esc_html( $file['file'] ); ?></code>
					</td>
					<td>
						<?php
						if ( $file['core_version'] && ( empty( $file['version'] ) || version_compare( $file['version'], $file['core_version'], '<' ) ) ) {
							$current_version = $file['version'] ? $file['version'] : '-';
							printf(
								/* Translators: %1$s: Template version, %2$s: Core version. */
								esc_html__( 'Version %1$s is out of date. The core version is %2$s', 'posterno' ),
								'<strong style="color:red">' . esc_html( $current_version ) . '</strong>',
								esc_html( $file['core_version'] )
							);
						}
						?>
					</td>
				</tr>

				<?php endforeach; ?>
			</tbody>
		</table>
		<?php

		return ob_get_clean();

	}

}

