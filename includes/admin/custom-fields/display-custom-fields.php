<?php
/**
 * Displays the content of the custom fields editor page.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Function responsible of displaying the custom fields page.
 * Actual output handled by vuejs.
 *
 * @since 0.1.0
 * @return void
 */
function pno_custom_fields_page() {
	echo '<div id="posterno-custom-fields-page"></div>';
}
