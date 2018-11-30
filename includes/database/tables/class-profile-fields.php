<?php
/**
 * Profile fields Table.
 *
 * @package     PNO
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace PNO\Database\Tables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Table;

/**
 * Setup the global "pno_profile_fields" database table
 *
 * @since 0.1.0
 */
final class Profile_Fields extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 0.1.0
	 * @var string
	 */
	protected $name = 'pno_profile_fields';

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
			user_meta_key longtext,
			settings longtext,
			PRIMARY KEY (id),
			KEY post_id (post_id)";
	}

}
