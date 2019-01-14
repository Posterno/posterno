<?php
/**
 * List of functions used for profiles.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function pno_get_queried_user_id() {

	$user_id = get_current_user_id();

	return $user_id;

}
