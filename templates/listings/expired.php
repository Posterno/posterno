<?php
/**
 * The template for displaying the content of expired listings.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/expired.php
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

posterno()->templates
	->set_template_data(
		[
			'type'    => 'info',
			'message' => esc_html__( 'Sorry, this listing is no longer available.', 'posterno' ),
		]
	)
	->get_template_part( 'message' );
