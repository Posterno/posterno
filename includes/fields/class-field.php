<?php
/**
 * Global fields object.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

use PNO\Base_Object;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines methods and properties for all fields of Posterno.
 */
class Field extends Base_Object {

	/**
	 * Field ID from the database this is the id of the post type.
	 *
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Meta id from the database, this is the id stored into the metadata table of the field type.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $meta_id = 0;

	/**
	 * Field meta key that will be used to store values into the database.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $meta = null;

	/**
	 * Field priority number used to determine the order of the field.
	 *
	 * @access protected
	 * @var int
	 */
	protected $priority = 0;

	/**
	 * Wether the field is a default field or not.
	 *
	 * @var boolean
	 */
	protected $default = false;

	/**
	 * The field type.
	 *
	 * @var boolean
	 */
	protected $type = false;

	/**
	 * The nicename of the field type.
	 *
	 * @var string
	 */
	protected $type_nicename = null;

	/**
	 * Field Name.
	 *
	 * @access protected
	 * @var string
	 */
	protected $name = null;

	/**
	 * Field form label.
	 *
	 * @access protected
	 * @var string
	 */
	protected $label = null;

	/**
	 * Field description.
	 *
	 * @access protected
	 * @var string
	 */
	protected $description = null;

	/**
	 * Field placeholder.
	 *
	 * @access protected
	 * @var string
	 */
	protected $placeholder = null;

	/**
	 * Determine wether the field is required or not.
	 *
	 * @var boolean
	 */
	protected $required = false;

	/**
	 * Selectable options for dropdown fields.
	 *
	 * @var mixed
	 */
	protected $selectable_options = false;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer db calls to be run.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	private $pending;

	/**
	 * Post type from where to grab the field.
	 *
	 * @var mixed
	 */
	public $post_type = null;

	/**
	 * Value associated to the field.
	 *
	 * @var mixed
	 */
	protected $value = false;

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @see set()
	 *
	 * @param string $key   Property name.
	 * @param mixed  $value Property value.
	 */
	public function __set( $key, $value ) {

		$key = sanitize_key( $key );

		// Only real properties can be saved.
		$keys = array_keys( get_class_vars( get_called_class() ) );

		if ( ! in_array( $key, $keys ) ) {
			return false;
		}

		$this->pending[ $key ] = $value;

		// Dispatch to setter method if value needs to be sanitized.
		if ( method_exists( $this, 'set_' . $key ) ) {
			return call_user_func( array( $this, 'set_' . $key ), $key, $value );
		} else {
			$this->{$key} = $value;
		}

	}

}
