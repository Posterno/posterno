<?php
/**
 * Responsible of validating submissions through a PNO\Form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form;

use PNO\Form\Field\AbstractField;
use PNO\Form\Rule\AbstractRule;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Validate submissions through a PNO\Form.
 */
class Validator {

	/**
	 * Individually validate a field.
	 *
	 * @param AbstractField $field the field to validate.
	 * @return mixed result of the validation.
	 */
	public static function validate_field( AbstractField $field ) {
		$errors = array();
		$result = new \stdClass();
		if ( $field->has_rules() ) {
			self::loop( $field, $field->get_rules(), $errors );
		}

		$result->valid = empty( $errors );
		$result->trace = $errors;

		return $result;
	}

	/**
	 * Loop through all the validation rules assigned to a form field and execute validation.
	 *
	 * @param AbstractField $field the field to validate.
	 * @param array         $rules list of validation rules applied to the field.
	 * @param array         $errors errors associated to the field if any.
	 * @return void
	 */
	private static function loop( AbstractField $field, array $rules, array &$errors ) {
		foreach ( $rules as $rule ) {
			if ( $rule->run( $field ) ) {
				$result = $rule->validate( $field );
				if ( is_bool( $result ) && $result === false ) {
					$errors[] = $rule->get_invalid_message();
				} elseif ( is_array( $result ) ) {
					self::loop( $field, $result, $errors );
				}
			}
		}
		return;
	}

}
