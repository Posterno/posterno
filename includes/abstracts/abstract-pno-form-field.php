<?php
/**
 * Abstract representation of a PNO\Form field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Field;

use PNO\Form;
use PNO\Form\Rule\AbstractRule;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Common methods and functionalities of a PNO\Form\Field
 */
abstract class AbstractField {

	/**
	 * Field name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Array of options of a field.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * The value of the field.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * The form assigned to the field.
	 *
	 * @var PNO\Form
	 */
	protected $parent;

	/**
	 * Get things started and create the field.
	 *
	 * @param string $name the name of the field.
	 * @param array  $options options assigned to the field.
	 */
	public function __construct( $name, array $options = array() ) {
		$this
			->set_name( $name )
			->set_options( $options )
			->init();
	}

	/**
	 * Get id of the field.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->name;
	}

	/**
	 * Set the name attribute of the field.
	 *
	 * @param string $name the name of the field.
	 * @return PNO\Form\Field
	 */
	public function set_name( $name ) {
		$this->name = str_replace( ' ', '_', $name );

		return $this;
	}

	/**
	 * Retrieve the name assigned to the field.
	 *
	 * @return string
	 */
	public function get_name() {
		return "{$this->parent->get_name()}[{$this->name}]";
	}

	/**
	 * Setup the options for the field.
	 *
	 * @param array $options the list of options to assign to the field.
	 * @return PNO\Form\Field
	 */
	public function set_options( array $options ) {
		$this->options = $options;

		return $this;
	}

	/**
	 * Add a new option to the field.
	 *
	 * @param string $name the name of the option.
	 * @param mixed  $value content of the option.
	 * @return PNO\Form\Field
	 */
	public function add_option( $name, $value ) {
		$this->options[ $name ] = $value;

		return $this;
	}

	/**
	 * Get the value of a single option for the field.
	 *
	 * @param string $name name of the option.
	 * @param mixed  $default default content if none has been set for the option.
	 * @return mixed
	 */
	public function get_option( $name, $default = null ) {
		return ( isset( $this->options[ $name ] ) ) ? $this->options[ $name ] : $default;
	}

	/**
	 * Retrieve all the options assigned to the field.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Programmatically set a value to the field.
	 *
	 * @param mixed $value the value to set to the field.
	 * @return PNO\Form\Field
	 */
	public function set_value( $value ) {
		$this->value = $value;

		return $this;
	}

	/**
	 * Get the value of the field.
	 *
	 * @return mixed
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Set to which form the field belongs to.
	 *
	 * @param Form $parent the class of the form.
	 * @return object
	 */
	public function set_parent( Form $parent ) {
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Retrieve the form to which the field's belongs to.
	 *
	 * @return object
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * Verify if the field has a label assigned.
	 *
	 * @return boolean
	 */
	public function has_label() {
		return ( $this->get_option( 'label', null ) ) ? true : false;
	}

	/**
	 * Get the label assigned to the field.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->get_option( 'label', '' );
	}

	/**
	 * Determine if the field has validation rules assigned.
	 *
	 * @return boolean
	 */
	public function has_rules() {
		return ( $this->get_option( 'rules', null ) ) ? true : false;
	}

	/**
	 * Retrieve a list of assigned rules to the field.
	 *
	 * @return AbstractRule[]
	 */
	public function get_rules() {
		return $this->get_option( 'rules', array() );
	}

	/**
	 * Determine if the field has any errors.
	 *
	 * @return boolean
	 */
	public function has_errors() {
		if ( $this->parent->has_errors() ) {
			return array_key_exists( $this->name, $this->parent->get_errors() );
		}

		return false;
	}

	/**
	 * Retrieve all the errors that belong to the field.
	 *
	 * @return array
	 */
	public function get_errors() {
		$errors = $this->parent->get_errors();
		return isset( $errors[ $this->name ] ) ? $errors[ $this->name ] : array();
	}

	/**
	 * Retrieve attributes that belong to the field.
	 *
	 * @param array $merge additional attributes that could belong to the field.
	 * @return mixed
	 */
	public function get_attributes( array $merge = array() ) {
		$result     = '';
		$attributes = $this->get_option( 'attributes', array() );
		if ( ! empty( $merge ) ) {
			$attributes = array_merge_recursive( $attributes, $merge );
		}
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $name => $value ) {
				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}
				$result .= " {$name}=\"{$value}\"";
			}
		}

		return $result;
	}

	/**
	 * Initialize the field.
	 *
	 * @return void
	 */
	abstract public function init();

	/**
	 * Bind a value of the field to the form.
	 *
	 * @param mixed $value the value to bind.
	 * @return void
	 */
	abstract public function bind( $value );

	/**
	 * Render the field on the frontend.
	 *
	 * @param array $attributes attributes to assign to the field.
	 * @return void
	 */
	abstract public function render( array $attributes = array() );

}
