<?php
/**
 * Representation of a multicheckbox field.
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
 * The class responsible of handling multiple checkboxes fields within a PNO\Form.
 */
class MultiCheckboxField extends AbstractGroup {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		parent::init();
		$this->set_type( 'multicheckbox' );
		return $this->set_value( $this->get_option( 'value', [] ) );
	}

	/**
	 * Bind the value of the field.
	 *
	 * @param string $value the value of the field.
	 * @return $this the current object.
	 */
	public function bind( $value ) {
		if ( $value ) {
			$valid = array();
			$value = ( is_array( $value ) ) ? $value : array( $value );
			foreach ( $value as $single ) {
				if ( $this->exists( $single ) ) {
					$valid[] = $single;
				}
			}
			return $this->set_value( $valid );
		}
		return $this->set_value( array() );
	}

	/**
	 * Verify if the choice exists.
	 *
	 * @param string $choice the choice to check.
	 * @return boolean
	 */
	private function exists( $choice ) {
		return array_key_exists( $choice, $this->choices );
	}

}
