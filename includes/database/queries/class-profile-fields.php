<?php
/**
 * Profile fields Query Class.
 *
 * @package     PNO
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Database\Queries;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Query;

/**
 * Class used for querying profile fields.
 *
 * @since 0.1.0
 *
 * @see \PNO\Database\Queries\Profile_Fields::__construct() for accepted arguments.
 */
class Profile_Fields extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'profile_fields';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'pf';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\PNO\\Database\\Schemas\\Profile_Fields';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'profile_field';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'profile_fields';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 0.1.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\PNO\\Field\\Profile';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'profile_fields';

	/** Methods ***************************************************************/

	/**
	 * Sets up the query, based on the query vars passed.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string|array $query the query arguments.
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
