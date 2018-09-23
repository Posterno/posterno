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
	 * Render the field on the frontend.
	 *
	 * @param array $attributes attributes to assign to the field.
	 * @return mixed
	 */
	public function render( array $attributes = array() ) {
		return '';
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
	protected function isValidChoice( $choice ) {
		return array_key_exists( $choice, $this->choices );
	}

	/**
	 * Render choices.
	 *
	 * @param string $choice the choice to render.
	 * @param array  $attributes attributes to assign to the choice field.
	 * @return mixed
	 */
	abstract public function renderChoice( $choice, array $attributes = array());

}
