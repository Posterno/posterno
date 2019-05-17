<?php
use function GuzzleHttp\json_decode;

/**
 * List of actions that interact with WordPress.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Load stuff after theme setup.
 *
 * @return void
 */
function pno_after_theme_setup_load() {

	\Carbon_Fields\Carbon_Fields::boot();
	$sidebar_manager = \Carbon_Fields\Carbon_Fields::resolve( 'sidebar_manager' );
	remove_action( 'admin_enqueue_scripts', array( $sidebar_manager, 'enqueue_scripts' ) );

	if ( ! current_theme_supports( 'menus' ) ) {
		add_theme_support( 'menus' );
	}

	register_nav_menu( 'pno-dashboard-menu', esc_html__( 'Posterno Dashboard Menu', 'posterno' ) );
	register_nav_menu( 'pno-profile-menu', esc_html__( 'Posterno Profile Menu', 'posterno' ) );

}
add_action( 'after_setup_theme', 'pno_after_theme_setup_load', 20 );

/**
 * Restrict access to the dashboard page only to logged in users.
 *
 * @return void
 */
function pno_restrict_dashboard_access() {

	$dashboard_page = pno_get_dashboard_page_id();

	if ( $dashboard_page && is_int( $dashboard_page ) && is_page( $dashboard_page ) && ! is_user_logged_in() ) {
		$login_page = pno_get_login_page_id();
		if ( $login_page && is_int( $login_page ) ) {
			$login_page = add_query_arg(
				[
					'redirect_to' => rawurlencode( get_permalink( $dashboard_page ) ),
					'restricted'  => true,
					'rpage_id'    => $dashboard_page,
				],
				get_permalink( $login_page )
			);
			wp_safe_redirect( $login_page );
			exit;
		}
	}

}
add_action( 'template_redirect', 'pno_restrict_dashboard_access' );

/**
 * Display a restricted access message at the top of the login form,
 * when a "restricted" query string is available within the url.
 *
 * @param string $form form object.
 * @return void
 */
function pno_display_restricted_access_message( $form ) {

	$page_id    = isset( $_GET['rpage_id'] ) ? absint( $_GET['rpage_id'] ) : false;
	$restricted = isset( $_GET['restricted'] ) ? true : false;

	if ( ! $page_id || ! $restricted ) {
		return;
	}

	$page_title = get_post_field( 'post_title', $page_id );

	$message = apply_filters(
		'pno_login_form_restricted_message',
		sprintf( __( 'You need to be logged in to access the "%1$s" page. Please login below or <a href="%2$s">register</a>.', 'posterno' ), $page_title, get_permalink( pno_get_registration_page_id() ) )
	);

	$data = [
		'message' => $message,
		'type'    => 'warning',
	];

	posterno()->templates
		->set_template_data( $data )
		->get_template_part( 'message' );

}
add_action( 'pno_before_login_form', 'pno_display_restricted_access_message', 10 );

/**
 * Loads the content for the dashboard tab called "dashboard".
 * By default this is the first tab.
 *
 * @return void
 */
function pno_load_initial_dashboard_content() {

	$data = [
		'user' => wp_get_current_user(),
	];

	if ( pno_user_has_submitted_listings( get_current_user_id() ) ) {
		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'dashboard/welcome' );
	} else {

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'logged-user' );

	}

}
add_action( 'pno_dashboard_tab_content_dashboard', 'pno_load_initial_dashboard_content' );

/**
 * Load the content for the account details tab within the dashboard page.
 *
 * @return void
 */
function pno_load_dashboard_account_details() {

	echo do_shortcode( '[pno_account_customization_form]' );

}
add_action( 'pno_dashboard_tab_content_edit-account', 'pno_load_dashboard_account_details' );

/**
 * Load the content for the password tab within the dashboard page.
 *
 * @return void
 */
function pno_load_dashboard_password_details() {

	echo do_shortcode( '[pno_change_password_form]' );

}
add_action( 'pno_dashboard_tab_content_password', 'pno_load_dashboard_password_details' );

/**
 * Load the content for the privacy tab within the dashboard page.
 *
 * @return void
 */
function pno_load_dashboard_privacy() {

	$confirmed_request = isset( $_GET['privacy_request'] ) && $_GET['privacy_request'] === 'confirmed' ? true : false;

	if ( $confirmed_request ) {

		$data = [
			'message' => esc_html__( 'Your request has been successfully confirmed, an administrator will send you an email with further details.', 'posterno' ),
			'type'    => 'success',
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'message' );

	}

	if ( pno_get_option( 'allow_data_request' ) ) {
		echo do_shortcode( '[pno_request_data_form]' );
	}

	if ( pno_get_option( 'allow_data_erasure' ) ) {
		echo do_shortcode( '[pno_request_data_erasure_form]' );
	}

	if ( pno_get_option( 'allow_account_delete' ) ) {
		echo do_shortcode( '[pno_delete_account_form]' );
	}

}
add_action( 'pno_dashboard_tab_content_privacy', 'pno_load_dashboard_privacy' );

/**
 * Load the content for the manage listings tab within the dashboard page.
 *
 * @return void
 */
function pno_load_manage_listings_dashboard() {

	$user_id = get_current_user_id();

	if ( ! pno_user_has_submitted_listings( $user_id ) || ! pno_can_user_submit_listings() ) {
		return;
	}

	$listings = pno_get_user_submitted_listings( $user_id );

	posterno()->templates
		->set_template_data(
			[
				'columns'         => pno_get_listings_table_columns(),
				'submission_page' => pno_get_listing_submission_page_id(),
				'listings'        => $listings,
			]
		)
		->get_template_part( 'dashboard/manage', 'listings' );

}
add_action( 'pno_dashboard_tab_content_listings', 'pno_load_manage_listings_dashboard' );

/**
 * Add plugin's version to header.
 *
 * @return void
 */
function pno_version_in_header() {
	echo '<meta name="generator" content="Posterno v' . esc_html( PNO_VERSION ) . '" />' . "\n";
}
add_action( 'wp_head', 'pno_version_in_header' );

/**
 * Adjust labels within the wp-login.php form to match
 * the type of login method selected in PNO.
 *
 * @return void
 */
function pno_adjust_wplogin_form_labels() {

	$login_method    = pno_get_option( 'login_method' );
	$translated_text = esc_html__( 'Username or email address', 'posterno' );

	if ( $login_method === 'username' ) {
		$translated_text = esc_html__( 'Username', 'posterno' );
	} elseif ( $login_method === 'email' ) {
		$translated_text = esc_html__( 'Email', 'posterno' );
	}

	add_filter(
		'gettext',
		function ( $t, $text, $domain ) use ( $translated_text ) {
			if ( 'Username or Email Address' === $text || 'Username' === $text ) {
				return $translated_text;
			}
			return $t;
		},
		20,
		3
	);

}
add_action( 'login_head', 'pno_adjust_wplogin_form_labels' );

/**
 * Redirect users to the login page if they're not registered and
 * accessing the submission page if restricted.
 *
 * @return void
 */
function pno_restrict_access_to_listings_submission_page() {

	$submission_page = pno_get_listing_submission_page_id();

	if ( $submission_page && is_int( $submission_page ) && is_page( $submission_page ) && ! is_user_logged_in() ) {
		$login_page = pno_get_login_page_id();
		if ( $login_page && is_int( $login_page ) ) {
			$login_page = add_query_arg(
				[
					'redirect_to' => rawurlencode( get_permalink( $submission_page ) ),
					'restricted'  => true,
					'rpage_id'    => $submission_page,
				],
				get_permalink( $login_page )
			);
			wp_safe_redirect( $login_page );
			exit;
		}
	}
}
add_action( 'template_redirect', 'pno_restrict_access_to_listings_submission_page' );

/**
 * Detect incoming privacy requests verifications when wp-login.php is disabled and redirect
 * to the member's privacy dashboard page after verification and approval.
 *
 * @return void
 */
function pno_detect_privacy_action_request() {

	if ( ! pno_get_option( 'redirect_wp_login' ) ) {
		return;
	}

	$action = isset( $_GET['action'] ) && $_GET['action'] === 'confirmaction' ? true : false;

	if ( ! $action ) {
		return;
	}

	$request_id  = isset( $_GET['request_id'] ) ? sanitize_text_field( $_GET['request_id'] ) : false;
	$request_key = isset( $_GET['confirm_key'] ) ? sanitize_text_field( $_GET['confirm_key'] ) : false;

	if ( $request_id && $request_key ) {

		$valid = wp_validate_user_request_key( $request_id, $request_key );

		if ( ! is_wp_error( $valid ) ) {

			_wp_privacy_account_request_confirmed( $request_id );

			_wp_privacy_send_request_confirmation_notification( $request_id );

			$url = add_query_arg( [ 'privacy_request' => 'confirmed' ], trailingslashit( get_permalink( pno_get_dashboard_page_id() ) . '/privacy' ) );
			wp_safe_redirect( $url );
			exit;
		}
	}

}
add_action( 'init', 'pno_detect_privacy_action_request' );

/**
 * Make sure that values submitted for some fields, actually match the options assigned to the field.
 *
 * @param \PNO\Form\Form $form form's object.
 * @throws \PNO\Exception When value does not match.
 * @return void
 */
function pno_force_validation_of_fields_options( \PNO\Form\Form $form ) {

	$types_to_validate = [ 'select', 'multiselect', 'multicheckbox', 'radio' ];

	foreach ( $form->getFields() as $key => $field ) {

		$type = $field->getType();

		if ( in_array( $type, $types_to_validate ) ) {

			$available_options = $field->getValues();
			$submitted_value   = $form->getFieldValue( $key );

			if ( ! empty( $submitted_value ) ) {
				if ( is_array( $submitted_value ) ) {
					foreach ( $submitted_value as $val ) {
						if ( ! array_key_exists( $val, $available_options ) ) {
							$field->setValue( false );
							throw new \PNO\Exception( sprintf( esc_html__( 'Value for the field "%s" is invalid.', 'posterno' ), $field->getLabel() ) );
						}
					}
				} else {
					if ( ! array_key_exists( $submitted_value, $available_options ) ) {
						$field->setValue( false );
						throw new \PNO\Exception( sprintf( esc_html__( 'Value for the field "%s" is invalid.', 'posterno' ), $field->getLabel() ) );
					}
				}
			}
		}
	}
}
add_action( 'pno_before_listing_submission', 'pno_force_validation_of_fields_options' );
add_action( 'pno_before_listing_editing', 'pno_force_validation_of_fields_options' );

/**
 * Make sure that a validation error appears when Vuejs fields are required and not filled.
 *
 * @param \PNO\Form\Form $form form's object.
 * @throws \PNO\Exception When value does not match.
 * @return void
 */
function pno_force_validation_of_vue_fields( \PNO\Form\Form $form ) {

	$types = [
		'listing-category',
		'listing-tags',
		'term-chain-dropdown',
		'listing-opening-hours',
		'listing-location',
	];

	foreach ( $form->getFields() as $key => $field ) {

		$type = $field->getType();

		if ( in_array( $type, $types ) && $field->isRequired() ) {

			$value = $form->getFieldValue( $key );

			if ( $type === 'listing-tags' ) {
				$value = json_decode( $value );
			}

			if ( empty( $value ) ) {
				throw new \PNO\Exception( sprintf( esc_html__( '"%s" is required.', 'posterno' ), $field->getLabel() ) );
			}
		}
	}
}
add_action( 'pno_before_listing_submission', 'pno_force_validation_of_vue_fields' );
add_action( 'pno_before_listing_editing', 'pno_force_validation_of_vue_fields' );
