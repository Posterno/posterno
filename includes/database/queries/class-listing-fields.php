<?php
/**
 * Listing fields Query Class.
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
 * Class used for querying listing fields.
 *
 * @since 0.1.0
 *
 * @see \PNO\Database\Queries\Listing_Fields::__construct() for accepted arguments.
 */
class Listing_Fields extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'listing_fields';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'lf';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\PNO\\Database\\Schemas\\Listing_Fields';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'listing_field';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'listing_fields';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 0.1.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\PNO\\Entities\\Field\\Listing';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'listing_fields';

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
