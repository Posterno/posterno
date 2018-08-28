<?php
/**
 * Adjustments Schema Class.
 *
 * @package     PNO
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Database\Schemas;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Schema;

/**
 * Adjustments Schema Class.
 *
 * @since 1.0.0
 */
final class Adjustments extends Schema {

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
			'name'       => 'parent',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'searchable' => true,
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'name',
			'type'       => 'varchar',
			'length'     => '200',
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'description',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true,
		),

		array(
			'uuid' => true,
		),
	);
}
