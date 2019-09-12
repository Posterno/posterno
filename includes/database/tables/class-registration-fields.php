<?php
/**
 * Registration fields Table.
 *
 * @package     PNO
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace PNO\Database\Tables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Table;

/**
 * Setup the global "pno_registration_fields" database table
 *
 * @since 0.1.0
 */
final class Registration_Fields extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 0.1.0
	 * @var string
	 */
	protected $name = 'pno_registration_fields';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 0.1.0
	 * @var int
	 */
	protected $version = 201808170001;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	protected $upgrades = [];

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 0.1.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL default '0',
			profile_field_id bigint(20) unsigned NOT NULL default '0',
			settings longtext,
			PRIMARY KEY (id),
			KEY post_id (post_id)";
	}

}
