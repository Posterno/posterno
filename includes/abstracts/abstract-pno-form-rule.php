<?php
/**
 * Abstract representation of a PNO\Form validation rule.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Rule;

use PNO\Form\Field\AbstractField;

/**
 * Main class responsible of definin common methods for validation rules.
 */
abstract class AbstractRule {

	/**
	 * The error message.
	 *
	 * @var string
	 */
	protected $invalid_message;

	/**
	 * Determine if the rule should be negated.
	 *
	 * @var boolean
	 */
	protected $negate;

	/**
	 * List of child rules.
	 *
	 * @var mixed
	 */
	protected $children = null;

	/**
	 * Initialize the validation rule.
	 *
	 * @param string  $invalid_message the error message.
	 * @param boolean $negate determine if rule should be negated.
	 */
	public function __construct( $invalid_message = '', $negate = false ) {
		$this->invalid_message = $invalid_message;
		$this->negate          = $negate;
	}

	/**
	 * Run validation rule.
	 *
	 * @param AbstractField $field the field to validate.
	 * @return mixed
	 */
	public function run( AbstractField $field ) {
		return true;
	}

	/**
	 * Set the validation error message.
	 *
	 * @param string $invalid_message the message to set.
	 * @return $this
	 */
	public function set_invalid_message( $invalid_message ) {
		$this->invalid_message = $invalid_message;

		return $this;
	}

	/**
	 * Get the defined validation error.
	 *
	 * @return string
	 */
	public function get_invalid_message() {
		return $this->invalid_message;
	}

	/**
	 * Validate the field.
	 *
	 * @param AbstractField $field the field to validate.
	 * @return mixed
	 */
	public function validate( AbstractField $field ) {
		return ( $this->negate ) ? ! $this->is_valid( $field ) : $this->is_valid( $field );
	}

	/**
	 * Determine the validation method.
	 *
	 * @param AbstractField $field the field to validate.
	 * @return mixed
	 */
	abstract public function is_valid( AbstractField $field );

}
