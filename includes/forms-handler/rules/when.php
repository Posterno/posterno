<?php
/**
 * Match multiple fields conditions for validation.
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
 * Conditionally verify multiple field together.
 */
class When extends AbstractRule {

	/**
	 * Conditon function.
	 *
	 * @var callable Condition function
	 */
	protected $condition;

	/**
	 * Get things started.
	 *
	 * @param callable $condition function.
	 * @param array    $children children conditons.
	 */
	public function __construct( callable $condition, array $children = array() ) {
		$this->condition = $condition;
		$this->children  = $children;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param AbstractField $field the field to verify.
	 */
	public function run( AbstractField $field ) {
		return call_user_func_array(
			$this->condition, array(
				$field->get_parent()->get_data(),
			)
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param  AbstractField $field the field to verify.
	 * @return array
	 */
	public function is_valid( AbstractField $field ) {
		return $this->children;
	}

}
