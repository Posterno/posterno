<?php
/**
 * Admin notices.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display a notice when the avatar field is disabled and the user is editing the field.
 *
 * @return void
 */
function pno_avatar_field_is_disabled_notice() {

	$screen = get_current_screen();

	if ( $screen instanceof WP_Screen && $screen->id == 'pno_users_fields' ) {

		global $post;

		$field = new PNO\Field\Profile( $post->ID );

		if ( $post instanceof WP_Post && isset( $post->ID ) && $field->get_object_meta_key() === 'avatar' && ! pno_get_option( 'allow_avatars' ) ) {

			$message = esc_html__( 'The avatar field is currently disabled. You can enable it through the plugin\'s settings.', 'posterno' );

			posterno()->admin_notices->register_notice( 'avatar_disabled', 'info', $message, [ 'dismissible' => false ] );

		}
	}

}
add_action( 'admin_head', 'pno_avatar_field_is_disabled_notice' );

/**
 * Display an error notice if the user has wrongly configured the password settings.
 *
 * @return void
 */
function pno_password_settings_controller_notice() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-options' ) {
		return;
	}

	if ( pno_get_option( 'disable_password' ) && pno_get_option( 'verify_password' ) ) {

		$message = esc_html__( 'Posterno: you have enabled the "Disable custom passwords during registration" setting, please disable the "Enable password confirmation" option.', 'posterno' );

		posterno()->admin_notices->register_notice( 'psw_setting_error', 'error', $message, [ 'dismissible' => false ] );

	}

}
add_action( 'admin_head', 'pno_password_settings_controller_notice' );

/**
 * Display a notice if avatars are currently globally disabled onto the site.
 *
 * @return void
 */
function pno_avatar_globally_disabled_notice() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-options' ) {
		return;
	}

	if ( pno_get_option( 'allow_avatars' ) && ! get_option( 'show_avatars' ) ) {

		$admin_url     = admin_url( 'options-discussion.php#show_avatars' );
		$pno_admin_url = admin_url( 'options-general.php?page=posterno-options' );

		$message = sprintf( __( 'Posterno: avatars <a href="%1$s">are currently globally disabled</a> onto your site, please <a href="%1$s">enable avatars</a> onto your site or <a href="%2$s">disable Posterno\'s built-in custom avatars</a>.', 'posterno' ), $admin_url, $pno_admin_url );

		posterno()->admin_notices->register_notice( 'avatar_setting_error', 'error', $message, [ 'dismissible' => false ] );

	}

}
add_action( 'admin_head', 'pno_avatar_globally_disabled_notice' );

/**
 * Display an error message when pages settings have more than one page selected.
 * Dirty solution since Carbon Fields currently doesn't provide a select "search" field,
 * therefore I'm forced to use a multiselect field to allow customers to search options.
 *
 * @return void
 */
function pno_required_pages_settings_is_singular_notice() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'posterno-options' ) {
		return;
	}

	$settings = [
		'login_page'                  => esc_html__( 'Login page', 'posterno' ),
		'password_page'               => esc_html__( 'Password recovery page', 'posterno' ),
		'registration_page'           => esc_html__( 'Registration page', 'posterno' ),
		'dashboard_page'              => esc_html__( 'Dashboard page', 'posterno' ),
		'submission_page'             => esc_html__( 'Listing submission page', 'posterno' ),
		'editing_page'                => esc_html__( 'Listing editing page', 'posterno' ),
		'profile_page'                => esc_html__( 'Public profile page', 'posterno' ),
		'terms_page'                  => esc_html__( 'Terms Page:', 'posterno' ),
		'login_redirect'              => esc_html__( 'After login', 'posterno' ),
		'logout_redirect'             => esc_html__( 'After logout', 'posterno' ),
		'registration_redirect'       => esc_html__( 'After registration', 'posterno' ),
		'cancellation_redirect'       => esc_html__( 'After account cancellation', 'posterno' ),
		'listing_submission_redirect' => esc_html__( 'After successful submission', 'posterno' ),
		'listing_editing_redirect'    => esc_html__( 'After successful editing', 'posterno' ),
	];

	$settings = apply_filters( 'pno_singular_page_options_list', $settings );

	foreach ( $settings as $key => $label ) {

		$option = pno_get_option( $key, false );

		if ( is_array( $option ) && count( $option ) > 1 ) {

			$message = sprintf( __( 'Posterno: the setting <strong>"%1$s"</strong> can only have 1 page selected. Please correct the issue by selecting only one page into the <a href="%2$s">settings panel</a>.', 'posterno' ), $label, admin_url( 'options-general.php?page=posterno-options' ) );

			posterno()->admin_notices->register_notice( "pno_setting_error_{$key}", 'error', $message, [ 'dismissible' => false ] );

		}
	}

}
add_action( 'admin_head', 'pno_required_pages_settings_is_singular_notice' );

/**
 * Display an error notice if the permalink structure needs to change.
 *
 * @return void
 */
function pno_permalink_controller_notice() {

	global $wp_rewrite;

	$screen = get_current_screen();

	$excluded = [
		'users_page_posterno-custom-profile-fields',
		'users_page_posterno-custom-registration-form',
		'listings_page_posterno-custom-listings-fields',
		'settings_page_posterno-options',
		'admin_page_posterno-options[accounts]',
		'admin_page_posterno-options[profiles]',
		'admin_page_posterno-options[emails]',
		'admin_page_posterno-options[listings]',
		'dashboard_page_pno-getting-started',
	];

	if ( in_array( $screen->id, $excluded ) ) {
		return;
	}

	if ( empty( $wp_rewrite->permalink_structure ) ) {

		$message = sprintf( __( '<strong>Posterno is almost ready</strong>. You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.', 'posterno' ), admin_url( 'options-permalink.php' ) );

		posterno()->admin_notices->register_notice( 'permalink_setting_error', 'error', $message, [ 'dismissible' => false ] );

	}

}
add_action( 'admin_head', 'pno_permalink_controller_notice' );

/**
 * Display a notice when a listing is successfully marked as expired in the admin panel.
 *
 * @return void
 */
add_action(
	'admin_notices',
	function () {

		$post_id = isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ? absint( $_GET['post'] ) : false;
		$trigger = isset( $_GET['marked-expired'] ) && $_GET['marked-expired'] === '1' ? true : false;

		if ( $post_id && $trigger && current_user_can( 'edit_posts' ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Listing successfully marked as expired.' ); ?></p>
			</div>
			<?php
		}

	}
);
