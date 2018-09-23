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
	 * @param string $value the value of the field.
	 * @return $this the current object.
	 */
	public function bind( $value ) {
		if ( $this->is_valid_choice( $value ) ) {
			return $this->set_value( $value );
		}
	}

}
