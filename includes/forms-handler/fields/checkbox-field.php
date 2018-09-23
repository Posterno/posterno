<?php
/**
 * Representation of a checkbox field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class responsible of handling checkbox fields within a PNO\Form.
 */
class CheckboxField extends AbstractField {

	/**
	 * Programmatically set a value to the field.
	 *
	 * @param mixed $value the value to set to the field.
	 * @return PNO\Form\Field
	 */
	public function set_value( $value ) {
		return parent::set_value( (bool) $value );
	}

	/**
	 * Initialize the field.
	 *
	 * @return void
	 */
	public function init() {
		$this->set_type( 'checkbox' );
		$this->set_value( (bool) $this->get_option( 'value', false ) );
	}
	/**
	 * {@inheritDoc}
	 *
	 * @param  string|null $value the value to bind.
	 * @return $this
	 */
	public function bind( $value ) {
		return $this->set_value( $value == 1 );
	}

	/**
	 * Determine if the checkbox is checked.
	 *
	 * @return string
	 */
	public function checked() {
		return $this->get_value() ? 'checked' : '';
	}
}
