<?php
/**
 * Responsible of generating a multiple choices field.
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
 * Generate a multiple choices field.
 */
abstract class AbstractGroup extends AbstractField {

	/**
	 * Choices assigned to the field.
	 *
	 * @var array
	 */
	protected $choices;

	/**
	 * Initialize the field's object.
	 *
	 * @return void
	 */
	public function init() {
		$this->choices = $this->get_option( 'choices', array() );
	}

	/**
	 * Get the choices assigned to the field.
	 *
	 * @return array
	 */
	public function get_choices() {
		return $this->choices;
	}

	/**
	 * Verify the selected choice is a choice assigned to the field.
	 *
	 * @param string $choice the selected choice to verify.
	 * @return boolean
	 */
	protected function is_valid_choice( $choice ) {
		return array_key_exists( $choice, $this->choices );
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

}
