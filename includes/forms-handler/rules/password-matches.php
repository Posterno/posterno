<?php
/**
 * Make sure passwords match during validation.
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
 * Verify that the submitted field is not empty.
 */
class PasswordMatches extends AbstractRule {

	/**
	 * Determine if the field is empty or not.
	 *
	 * @param AbstractField $field the field we're verifying.
	 * @return boolean
	 */
	public function is_valid( AbstractField $field ) {

		$password_1 = $field->get_parent()->get_data()['password'];
		$password_2 = $field->get_parent()->get_data()['password_confirm'];

		if ( $password_1 !== $password_2 ) {
			$this->set_invalid_message( esc_html__( 'Passwords do not match.' ) );
			return false;
		}

		return true;
	}
}
