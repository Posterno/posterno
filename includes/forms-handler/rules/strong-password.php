<?php
/**
 * Make sure passwords are strong.
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
 * Verify that the submitted passwords are strong.
 */
class StrongPassword extends AbstractRule {

	/**
	 * Determine if the password is strong enough.
	 *
	 * @param AbstractField $field the field we're verifying.
	 * @return boolean
	 */
	public function is_valid( AbstractField $field ) {

		$contains_letter  = preg_match( '/[A-Z]/', $field->get_value() );
		$contains_digit   = preg_match( '/\d/', $field->get_value() );
		$contains_special = preg_match( '/[^a-zA-Z\d]/', $field->get_value() );

		if ( ! $contains_letter || ! $contains_digit || ! $contains_special || strlen( $field->get_value() ) < 8 ) {
			$this->set_invalid_message( esc_html__( 'Password must be at least 8 characters long and contain at least 1 number, 1 uppercase letter and 1 special character.' ) );
		}

		return false;
	}
}
