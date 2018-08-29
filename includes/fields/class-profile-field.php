<?php
/**
 * Profile field object.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\sss;

use PNO\Base_Object;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Profile_Field extends Base_Object {

	/**
	 * Comment type.
	 *
	 * @since 3.0
	 * @var string
	 */
	public $comment_type = '';

}
