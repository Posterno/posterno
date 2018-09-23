<?php
/**
 * Representation of a dropdown field.
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
 * The class responsible of handling dropdown fields within a PNO\Form.
 */
class DropdownField extends AbstractGroup {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		parent::init();
		$this->set_type( 'dropdown' );
		return $this->set_value( $this->get_option( 'value', '' ) );
	}

	/**
	 * Bind the value of the field.
	 *
	 * @param string $choices the choices of the field.
	 * @return $this the current object.
	 */
	public function bind( $choices ) {
		$value   = array();
		$choices = is_array( $choices ) ? $choices : array();
		foreach ( $choices as $choice => $label ) {
			if ( $this->is_valid_choice( $choice ) ) {
				$value[] = $choice;
			}
		}
		return $this->set_value( $value );
	}

	/**
	 * Verify the enabled option.
	 *
	 * @param  string $choice the tested choice.
	 * @return string
	 */
	private function checked( $choice ) {
		return in_array( $choice, $this->value ) ? 'checked' : '';
	}

}
