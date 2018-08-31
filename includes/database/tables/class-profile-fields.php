<?php
/**
 * Profile fields table.
 *
 * @package     PNO
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Database\Tables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Table;

/**
 * Setup the global "pno_profile_fields" database table
 *
 * @since 1.0.0
 */
final class Profile_Fields extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var string
	 */
	protected $name = 'pno_profile_fields';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var int
	 */
	protected $version = 201807270003;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $upgrades = array();

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			object_id bigint(20) unsigned NOT NULL default '0',
			type varchar(20) NOT NULL default '',
			required bool NOT NULL DEFAULT '0',
			priority bigint(20) unsigned NOT NULL default '0',
			meta_key longtext NOT NULL default '',
			classes longtext default NULL,
			label longtext default NULL,
			placeholder longtext default NULL,
			description longtext default NULL,
			admin_only bool NOT NULL DEFAULT '0',
			read_only bool NOT NULL DEFAULT '0',
			options longtext default NULL,
			PRIMARY KEY (id),
			KEY object_id_type (object_id,type(20))";
	}
}
