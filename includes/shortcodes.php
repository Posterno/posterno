<?php
/**
 * Shortcodes definition
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

function pno_login_form() {

	echo posterno()->forms->get_form( 'login', [] );

}
add_shortcode( 'pno_login_form', 'pno_login_form' );
