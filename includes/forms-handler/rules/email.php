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
 * Verify that the submitted field value is an email address.
 */
class Email extends AbstractRule {

	/**
	 * Determine if the field value is an email address.
	 *
	 * @param AbstractField $field the field we're verifying.
	 * @return boolean
	 */
	public function is_valid( AbstractField $field ) {

		if ( empty( $this->get_invalid_message() ) ) {
			$this->set_invalid_message( esc_html__( 'Not a valid email address.' ) );
		}

		return is_email( $field->get_value() );
	}
}
