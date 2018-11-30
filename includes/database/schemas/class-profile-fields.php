<?php
/**
 * Profile fields Schema Class.
 *
 * @package     PNO
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace PNO\Database\Schemas;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Schema;

/**
 * Profile fields Schema Class.
 *
 * @since 0.1.0
 */
class Profile_Fields extends Schema {

	/**
	 * Array of database column objects.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    array
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
			'name'      => 'post_id',
			'type'      => 'bigint',
			'length'    => '20',
			'unsigned'  => true,
			'default'   => '0',
			'cache_key' => true,
		),

		array(
			'name'       => 'user_meta_key',
			'type'       => 'longtext',
			'searchable' => false,
			'sortable'   => false,
		),

		array(
			'name'       => 'settings',
			'type'       => 'longtext',
			'searchable' => false,
			'sortable'   => false,
		),

	);
}
