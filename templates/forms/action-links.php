<?php
/**
 * The template for displaying action links within the forms.
 *
 * This template can be overridden by copying it to yourtheme/posterno/forms/action-links.php
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

<ul class="pno-action-links">
	<?php if ( isset( $data->login_link ) && $data->login_link === true ) : ?>
		<li>
			<?php echo apply_filters( 'pno_login_link_label', sprintf( __( 'Already have an account? <a href="%s">Sign In &raquo;</a>' ), esc_url( get_permalink( pno_get_login_page_id() ) ) ) ); ?>
		</li>
	<?php endif; ?>
	<?php if ( isset( $data->register_link ) && $data->register_link === true ) : ?>
		<li>
			<?php echo apply_filters( 'pno_registration_link_label', sprintf( __( 'Don\'t have an account? <a href="%s">Signup Now &raquo;</a>' ), esc_url( get_permalink( pno_get_registration_page_id() ) ) ) ); ?>
		</li>
	<?php endif; ?>
	<?php if ( isset( $data->psw_link ) && $data->psw_link === true ) : ?>
		<li>
			<a href="<?php echo esc_url( get_permalink( pno_get_password_recovery_page_id() ) ); ?>">
				<?php echo apply_filters( 'pno_password_link_label', __( 'Lost your password?' ) ); ?>
			</a>
		</li>
	<?php endif; ?>
</ul>
