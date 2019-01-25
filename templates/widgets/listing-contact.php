<?php
/**
 * The template for displaying the content of the listing contact form widget.
 *
 * This template can be overridden by copying it to yourtheme/pno/widgets/listing-contact.php
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

echo posterno()->forms->get_form( 'listing-contact' );
