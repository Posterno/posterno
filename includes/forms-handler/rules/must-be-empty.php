<?php
/**
 * Validation rule for posterno's forms.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Rule;

use PNO\Form\Field\AbstractField;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Verify that the submitted field stays empty.
 */
class MustBeEmpty extends AbstractRule {

	/**
	 * Determine if the field is empty or not.
	 *
	 * @param AbstractField $field the field we're verifying.
	 * @return boolean
	 */
	public function is_valid( AbstractField $field ) {
		$value = $field->get_value();
		$valid = true;
		if ( ! empty( $field->get_value() ) ) {
			$valid = false;
		}
		return $valid;
	}
}
