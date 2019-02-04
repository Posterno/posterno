<?php
/**
 * The template for a quick intro to the currently logged in user.
 *
 * This template can be overridden by copying it to yourtheme/posterno/logged-user.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<div class="alert alert-info" role="alert">
	<p><?php printf( __( 'Hello %1$s ( not %1$s? <a href="%2$s" class="alert-link">Log out</a> )' ), '<strong>' . $data->user->display_name . '</strong>', wp_logout_url() ); ?></p>
	<p class="mb-0">
		<?php
		printf(
			__( 'From your account dashboard you can <a href="%1$s">manage your listings</a>, <a href="%2$s">edit your password</a> and <a href="%3$s">customize your account details</a>.' ),
			pno_get_dashboard_navigation_item_url( 'manage-listings' ),
			pno_get_dashboard_navigation_item_url( 'password' ),
			pno_get_dashboard_navigation_item_url( 'edit-account' )
		);
		?>
	</p>
</div>
