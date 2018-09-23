<?php
/**
 * Abstract representation of a PNO\Form layout.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Layout;

use PNO\Form\Field\AbstractField;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Representation of a form layout.
 */
abstract class AbstractLayout {

	/**
	 * Abstract representation of the render functionality of a field.
	 *
	 * @param AbstractField $field the field to render.
	 * @return void
	 */
	abstract public function render_field( AbstractField $field);
}
