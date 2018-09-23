<?php
/**
 * Representation of a textarea editor field.
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
 * The class responsible of handling textarea editor fields within a PNO\Form.
 */
class EditorField extends TextAreaField {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		$this->set_type( 'editor' );
		return $this->set_value( $this->get_option( 'value', '' ) );
	}

}
