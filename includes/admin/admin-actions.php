<?php
/**
 * Registers all the actions for the administration.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Determines when the custom shortcodes editor can be loaded.
 *
 * @access public
 * @since  0.1.0
 * @return void
 */
function pno_shortcodes_add_mce_button() {

	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'pno_shortcodes_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'pno_shortcodes_register_mce_button' );
	}
}
add_action( 'admin_head', 'pno_shortcodes_add_mce_button' );

/**
 * Adds js strings to the footer so the shortcodes editor is translatable.
 *
 * @return void
 */
function pno_localize_tinymce_editor() {

	$js_vars = [
		'title' => esc_html__( 'Posterno shortcodes' ),
		'forms' => [
			'title'        => esc_html__( 'Forms' ),
			'login'        => esc_html__( 'Login form' ),
			'registration' => esc_html__( 'Registration form' ),
			'password'     => esc_html__( 'Password recovery form' ),
			'submission'   => esc_html__( 'Listing submission form' ),
			'editing'      => esc_html__( 'Listing editing form' ),
		],
		'links' => [
			'title'  => esc_html__( 'Links' ),
			'login'  => [
				'title'    => esc_html__( 'Login link' ),
				'redirect' => esc_html__( 'Redirect after login (optional)' ),
				'label'    => esc_html__( 'Link Label' ),
			],
			'logout' => [
				'title'    => esc_html__( 'Logout link' ),
				'redirect' => esc_html__( 'Redirect after logout (optional)' ),
				'label'    => esc_html__( 'Link Label' ),
			],
		],
		'pages' => [
			'title'     => esc_html__( 'Pages' ),
			'dashboard' => esc_html__( 'Dashboard' ),
			'profile'   => esc_html__( 'Profile' ),
		],
	];

	?>
	<script type="text/javascript">
		var pnotinymce = <?php echo json_encode( $js_vars ); ?>
	</script>
	<?php

}
add_action( 'admin_footer', 'pno_localize_tinymce_editor' );

/**
 * Hides certain settings of custom fields post type.
 *
 * @return void
 */
function pno_hide_custom_fields_pt_settings() {

	global $post_type;

	$post_types = array(
		'pno_users_fields',
		'pno_signup_fields',
		'pno_emails',
		'pno_listings_fields',
	);

	if ( in_array( $post_type, $post_types ) ) {
		echo '
		<style type="text/css">
			#post-preview, #view-post-btn,
			#misc-publishing-actions #visibility,
			#misc-publishing-actions .misc-pub-curtime,
			.page-title-action {
				display: none;
			}

			.pno-field-is-default-notice {
				background: #e5f5fa;
				padding: 10px !important;
				margin: -10px -12px -16px !important;
			}

		</style>';
	}
}
add_action( 'admin_head-post-new.php', 'pno_hide_custom_fields_pt_settings' );
add_action( 'admin_head-post.php', 'pno_hide_custom_fields_pt_settings' );

/**
 * Add link back to the fields list table within the post type editing screen.
 *
 * @return void
 */
function pno_after_custom_fields_post_title() {

	global $post;

	if ( $post instanceof WP_Post && isset( $post->post_type ) && $post->post_type == 'pno_users_fields' ) {

		$admin_url = admin_url( 'users.php?page=posterno-custom-profile-fields' );
		echo '<br/><span class="dashicons dashicons-editor-table"></span> <a href="' . esc_url( $admin_url ) . '">' . esc_html__( 'All profile fields' ) . '</a>';

	} elseif ( $post instanceof WP_Post && isset( $post->post_type ) && $post->post_type == 'pno_signup_fields' ) {

		$admin_url = admin_url( 'users.php?page=posterno-custom-registration-form' );
		echo '<br/><span class="dashicons dashicons-editor-table"></span> <a href="' . esc_url( $admin_url ) . '">' . esc_html__( 'All registration form fields' ) . '</a>';

	} elseif ( $post instanceof WP_Post && isset( $post->post_type ) && $post->post_type == 'pno_listings_fields' ) {

		$admin_url = admin_url( 'edit.php?post_type=listings&page=posterno-custom-listings-fields' );
		echo '<br/><span class="dashicons dashicons-editor-table"></span> <a href="' . esc_url( $admin_url ) . '">' . esc_html__( 'All listings fields' ) . '</a>';

	}

}
add_action( 'edit_form_before_permalink', 'pno_after_custom_fields_post_title' );

/**
 * Force wipe of custom field when trashed.
 *
 * @param string $post_id post id.
 * @return void
 */
function pno_force_delete_on_custom_fields_trash( $post_id ) {

	if ( get_post_type( $post_id ) === 'pno_users_fields' ) {
		$field = new PNO\Field\Profile( $post_id );
		$field->delete();
		wp_safe_redirect( admin_url( 'users.php?page=posterno-custom-profile-fields&trashed=true' ) );
		exit;
	} elseif ( get_post_type( $post_id ) === 'pno_signup_fields' ) {
		$field = new PNO\Field\Registration( $post_id );
		$field->delete();
		wp_safe_redirect( admin_url( 'users.php?page=posterno-custom-registration-form&trashed=true' ) );
		exit;
	} elseif ( get_post_type( $post_id ) === 'pno_listings_fields' ) {
		$field = new PNO\Field\Listing( $post_id );
		$field->delete();
		wp_safe_redirect( admin_url( 'edit.php?post_type=listings&page=posterno-custom-listings-fields&trashed=true' ) );
		exit;
	}

}
add_action( 'wp_trash_post', 'pno_force_delete_on_custom_fields_trash', 9999 );

/**
 * Display the list of listings post statuses into the admin panel.
 *
 * @return void
 */
function pno_display_listings_post_statuses_list() {

	global $post, $post_type;

	// Abort if we're on the wrong post type, but only if we got a restriction.
	if ( 'listings' !== $post_type ) {
		return;
	}

	// Get all non-builtin post status and add them as <option>.
	$options = '';
	$display = '';

	foreach ( pno_get_listing_post_statuses() as $status => $name ) {
		$selected = selected( $post->post_status, $status, false );
		// If we one of our custom post status is selected, remember it.
		if ( $selected ) {
			$display = $name;
		}
		// Build the options.
		$options .= "<option{$selected} value='{$status}'>" . esc_html( $name ) . '</option>';
	}
	?>
	<script type="text/javascript">
		jQuery( document ).ready( function($) {
			<?php if ( ! empty( $display ) ) : ?>
				jQuery( '#post-status-display' ).html( <?php echo wp_json_encode( $display ); ?> );
			<?php endif; ?>
			var select = jQuery( '#post-status-select' ).find( 'select' );
			jQuery( select ).html( <?php echo wp_json_encode( $options ); ?> );
		} );
	</script>
	<?php
}
foreach ( array( 'post', 'post-new' ) as $hook ) {
	add_action( "admin_footer-{$hook}.php", 'pno_display_listings_post_statuses_list' );
}

/**
 * Define the content for the custom column for the pno emails post type.
 *
 * @param string $column the name of the column.
 * @param string $post_id the post we're going to use.
 * @return void
 */
function pno_emails_post_type_columns_content( $column, $post_id ) {
	if ( 'situations' !== $column ) {
		return;
	}

	// Grab email situations for the current post.
	$terms = get_the_terms( $post_id, 'pno-email-type' );

	if ( $terms ) {

		$situations = wp_list_pluck( $terms, 'description' );

		// Output each situation as a list item.
		echo '<ul style="margin-top:0;"><li>';
		echo implode( '</li><li>', $situations ); //phpcs:ignore
		echo '</li></ul>';
	}

}
add_action( 'manage_pno_emails_posts_custom_column', 'pno_emails_post_type_columns_content', 10, 2 );

/**
 * Removes listings from the list of post types that support "View Mode" option.
 *
 * @param array $post_types Array of post types that support view mode.
 * @return array
 */
function pno_disable_listings_post_type_view_mode( $post_types ) {

	unset( $post_types['listings'] );

	return $post_types;

}
add_action( 'view_mode_post_types', 'pno_disable_listings_post_type_view_mode' );

/**
 * Add a checkbox to the listings publish box letting administrators trigger the listings approval notification email.
 *
 * @param object $post the post object.
 * @return void
 */
function pno_trigger_administrator_approval_email( $post ) {

	$post_type = get_post_type( $post );

	if ( $post_type !== 'listings' || get_post_status( $post ) !== 'pending' ) {
		return;
	}

	$output = '<div class="carbon-field carbon-checkbox pno-publish-action"><div class="field-holder"><label><input type="checkbox" name="_listing_trigger_approval_email">' . esc_html__( 'Send approval notification' ) . '</label></div><em class="carbon-help-text">' . esc_html__( 'Enable the option to notify the author that the listing has been approved.' ) . '</em></div>';

	echo $output; //phpcs:ignore

}
add_action( 'post_submitbox_misc_actions', 'pno_trigger_administrator_approval_email', 10, 1 );

/**
 * Send approval notification to the author of a listing if the administrator has approved the listing.
 *
 * @param string $post_id the id of the listing.
 * @return void
 */
function pno_send_administrator_approval_email( $post_id ) {

	$post_type = get_post_type( $post_id );

	if ( $post_type !== 'listings' ) {
		return;
	}

	$trigger = isset( $_POST['_listing_trigger_approval_email'] ) && $_POST['_listing_trigger_approval_email'] === 'on' ? true : false;

	if ( $trigger ) {

		$author_id = get_post_field( 'post_author', $post_id );
		$author    = get_user_by( 'id', $author_id );

		if ( $author instanceof WP_User ) {
			pno_send_email(
				'core_user_listing_approved',
				$author->data->user_email,
				[
					'user_id'    => $author_id,
					'listing_id' => $post_id,
				]
			);
		}
	}

}
add_action( 'save_post', 'pno_send_administrator_approval_email', 10, 1 );
