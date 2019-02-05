<?php
/**
 * The template for displaying the content of the listing contact form widget.
 *
 * This template can be overridden by copying it to yourtheme/posterno/widgets/listing-contact.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 * @package posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$login_required = isset( $data->require_login ) && $data->require_login === true ? true : false;

if ( $login_required && ! is_user_logged_in() ) {

	$login_page    = add_query_arg( [ 'redirect_to' => get_permalink() ], get_permalink( pno_get_login_page_id() ) );
	$register_page = add_query_arg( [ 'redirect_to' => get_permalink() ], get_permalink( pno_get_registration_page_id() ) );

	posterno()->templates
		->set_template_data(
			[
				'type'    => 'warning',
				'message' => sprintf( __( 'You need to be logged in to contact this listing\'s author. Please <a href="%1$s">login</a> or <a href="%2$s">register</a>.', 'posterno' ), esc_url( $login_page ), esc_url( $register_page ) ),
			]
		)
		->get_template_part( 'message' );
		return;

} else {

	echo posterno()->forms->get_form( 'listing-contact' );

}


