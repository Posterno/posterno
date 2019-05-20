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
			$result = self::failing_test( $name, esc_html__( 'Update your permalink structure.' ), sprintf( __( '<strong>Posterno is almost ready</strong>. You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.', 'posterno' ), admin_url( 'options-permalink.php' ) ) );
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
			$result = self::failing_test( $name, esc_html__( 'A login page has not been created.' ), sprintf( __( 'You must <a href="%s" target="_blank">setup a login page.</a>', 'posterno' ), 'https://docs.posterno.com/article/460-login-page-setup' ) );
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
			$result = self::failing_test( $name, esc_html__( 'A password recovery page has not been created.' ), sprintf( __( 'You must <a href="%s" target="_blank">setup a password recovery page.</a>', 'posterno' ), 'https://docs.posterno.com/article/461-password-recovery-page-setup' ) );
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
			$result = self::failing_test( $name, esc_html__( 'A registration page has not been created.' ), sprintf( __( 'You must <a href="%s" target="_blank">setup a registration page.</a>', 'posterno' ), 'https://docs.posterno.com/article/462-registration-page-setup' ) );
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
			$result = self::failing_test( $name, esc_html__( 'A dashboard page has not been created.' ), sprintf( __( 'You must <a href="%s" target="_blank">setup a dashboard page.</a>', 'posterno' ), 'https://docs.posterno.com/article/463-dashboard-page-setup' ) );
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
			$result = self::failing_test( $name, esc_html__( 'A listing submission page has not been created.' ), sprintf( __( 'You should <a href="%s" target="_blank">setup a listing submission page,</a> if you wish your members to submit listings from the frontend.', 'posterno' ), 'https://docs.posterno.com/article/464-listing-submission-page-setup' ), false, 'recommended' );
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
			$result = self::failing_test( $name, esc_html__( 'A listing editing page has not been created.' ), sprintf( __( 'You should <a href="%s" target="_blank">setup a listing editing page,</a> if you wish your members to edit listings from the frontend.', 'posterno' ), 'https://docs.posterno.com/article/465-listing-editing-page-setup' ), false, 'recommended' );
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
			$result = self::failing_test( $name, esc_html__( 'A public profile page has not been created.' ), sprintf( __( 'You should <a href="%s" target="_blank">setup a public profile page</a>, If you wish your members to view profiles of other members on your site.', 'posterno' ), 'https://docs.posterno.com/article/466-public-profile-page-setup' ), false, 'recommended' );
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
	public function test__default_data_has_been_correctly_installed() {

		$name = __FUNCTION__;

		$components = posterno()->components;

		foreach ( $components as $component ) {

			$object = $component->get_interface( 'table' );

			if ( $object instanceof \PNO\Database\Table && ! $object->exists() ) {
				return self::failing_test( $name, esc_html__( 'Some default data is missing.' ), esc_html__( 'Please contact support.' ) );
			} elseif ( $object instanceof \PNO\Database\Table && $object->exists() ) {
				$result = self::passing_test( $name );
			}

		}

		return $result;

	}

}

