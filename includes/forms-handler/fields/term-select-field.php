<?php
/**
 * Representation of a term selection field.
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
 * The class responsible of handling term selection fields within a PNO\Form.
 */
class TermSelectField extends TextField {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		$this->set_type( 'term-select' );
		return $this->set_value( $this->get_option( 'value', '' ) );
	}

}
