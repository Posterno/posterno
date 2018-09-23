<?php
/**
 * Representation of a text field.
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
 * The class responsible of handling text fields within a PNO\Form.
 */
class TextField extends AbstractField {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {

		$this->set_type( 'text' );

		return $this->set_value( $this->get_option( 'value', '' ) );
	}

	/**
	 * Bind the value of the field.
	 *
	 * @param string $value the value of the field.
	 * @return $this the current object.
	 */
	public function bind( $value ) {
		return $this->set_value( $value );
	}

	/**
	 * Render the field on the frontend.
	 *
	 * @param array $attributes attributes to assign to the field.
	 * @return mixed
	 */
	public function render( array $attributes = array() ) {
		return "<input type=\"text\" name=\"{$this->get_name()}\" value=\"{$this->get_value()}\" {$this->get_attributes($attributes)} />";
	}

}
