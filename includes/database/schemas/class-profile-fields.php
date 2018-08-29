<?php
/**
 * Profile fields schema class.
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
 * Profile fields schema class.
 *
 * @since 1.0.0
 */
class Profile_Fields extends Schema {

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
			'name'     => 'object_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		),

		array(
			'name'    => 'type',
			'type'    => 'varchar',
			'length'  => '20',
			'default' => '',
		),

		array(
			'name'    => 'required',
			'type'    => 'bool',
			'default' => '0',
		),

		array(
			'name'     => 'priority',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		),

		array(
			'name'    => 'meta_key',
			'type'    => 'longtext',
			'default' => '',
		),

		array(
			'name'    => 'classes',
			'type'    => 'longtext',
			'default' => null,
		),

		array(
			'name'    => 'label',
			'type'    => 'longtext',
			'default' => null,
		),

		array(
			'name'    => 'placeholder',
			'type'    => 'longtext',
			'default' => null,
		),

		array(
			'name'    => 'description',
			'type'    => 'longtext',
			'default' => null,
		),

		array(
			'name'    => 'admin_only',
			'type'    => 'bool',
			'default' => '0',
		),

		array(
			'name'    => 'read_only',
			'type'    => 'bool',
			'default' => '0',
		),

		array(
			'uuid' => false,
		),
	);
}
