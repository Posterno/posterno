<?php
/**
 * Representation of a file dropzone field.
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
 * The class responsible of handling file fields within a PNO\Form.
 */
class DropzoneField extends FileField {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		$this->set_type( 'dropzone' );
		return $this->set_value( $this->get_option( 'value', [] ) );
	}

}
