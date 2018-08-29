<?php
/**
 * Profile fields query class.
 *
 * @package     PNO
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Database\Queries;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PNO\Database\Query;

/**
 * Class used for querying items.
 *
 * @since 1.0.0
 *
 * @see \PNO\Database\Queries\Profile_Field::__construct() for accepted arguments.
 */
class Profile_Field extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'profile_fields';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'pf';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\PNO\\Database\\Schemas\\Profile_Fields';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'profile_field';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'profile_fields';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 1.0.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\PNO\\Fields\\Profile_Field';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'profile_fields';

	/** Methods ***************************************************************/

	/**
	 * Sets up the query, based on the query vars passed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of query parameters. Default empty.
	 *
	 *     @type int          $id                   An note ID to only return that order. Default empty.
	 *     @type array        $id__in               Array of note IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of note IDs to exclude. Default empty.
	 *     @type string       $object_id            An object ID to only return those objects. Default empty.
	 *     @type array        $object_id__in        Array of object IDs to include. Default empty.
	 *     @type array        $object_id__not_in    Array of IDs object to exclude. Default empty.
	 *     @type string       $object_type          An object types to only return that type. Default empty.
	 *     @type array        $object_type__in      Array of object types to include. Default empty.
	 *     @type array        $object_type__not_in  Array of object types to exclude. Default empty.
	 *     @type string       $user_id              A user ID to only return those users. Default empty.
	 *     @type array        $user_id__in          Array of user IDs to include. Default empty.
	 *     @type array        $user_id__not_in      Array of user IDs to exclude. Default empty.
	 *     @type array        $content              Content to query by. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a count (true) or array of objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete objects). Default empty.
	 *     @type int          $number               Limit number of notes to retrieve. Default 100.
	 *     @type int          $offset               Number of notes to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'object_id', 'object_type', 'user_id', 'date_created',
	 *                                              'user_id__in', 'object_id__in', 'object_type__in'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching notes for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found notes. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}
