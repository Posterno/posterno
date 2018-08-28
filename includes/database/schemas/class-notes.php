<?php
/**
 * Notes Schema Class.
 *
 * @package     PNO
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace PNO\Database\Schemas;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Schema;

/**
 * Notes Schema Class.
 *
 * @since 1.0.0
 */
class Notes extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since 1.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		),

		array(
			'name'     => 'user_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
		),

		array(
			'uuid' => true,
		),
	);
}
