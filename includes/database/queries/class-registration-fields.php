<?php
/**
 * Registration fields Query Class.
 *
 * @package     PNO
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Database\Queries;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Query;

/**
 * Class used for querying registration fields.
 *
 * @since 0.1.0
 *
 * @see \PNO\Database\Queries\Registration_Fields::__construct() for accepted arguments.
 */
class Registration_Fields extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'registration_fields';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'rf';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\PNO\\Database\\Schemas\\Registration_Fields';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'registration_field';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'registration_fields';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 0.1.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\PNO\\Entities\Field\\Registration';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'registration_fields';

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
