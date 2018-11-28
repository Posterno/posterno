<?php
/**
 * Registration fields Query Class.
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
	protected $item_shape = '\\PNO\\Customers\\Email_Address';

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
	 * Sets up the customer query, based on the query vars passed.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of customer query parameters. Default empty.
	 *
	 *     @type int          $id                   An customer ID to only return that customer. Default empty.
	 *     @type array        $id__in               Array of customer IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of customer IDs to exclude. Default empty.
	 *     @type int          $customer_id          A customer ID to only return that object. Default empty.
	 *     @type array        $customer_id__in      Array of customer IDs to include. Default empty.
	 *     @type array        $customer_id__not_in  Array of customer IDs to exclude. Default empty.
	 *     @type string       $type                 Limit results to those affiliated with a given type. Default empty.
	 *     @type array        $type__in             Array of types to include affiliated orders for. Default empty.
	 *     @type array        $type__not_in         Array of types to exclude affiliated orders for. Default empty.
	 *     @type string       $status               An address statuses to only return that address. Default empty.
	 *     @type array        $status__in           Array of address statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of address statuses to exclude. Default empty.
	 *     @type string       $email                An email address to only return that email address. Default empty.
	 *     @type array        $email__in            Array of email addresses to include. Default empty.
	 *     @type array        $email__not_in        Array of email addresses to exclude. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit customers by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a customer count (true) or array of customer objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete customer objects). Default empty.
	 *     @type int          $number               Limit number of customers to retrieve. Default 100.
	 *     @type int          $offset               Number of customers to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'date_created', 'start_date', 'end_date'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching customers for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found customers. Default false.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}
}