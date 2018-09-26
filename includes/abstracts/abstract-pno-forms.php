<?php
/**
 * Abstract representation of a PNO\Forms.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

use PNO\Form;

/**
 * Abstract definition of a Posterno's frontend form.
 */
abstract class Forms {

	/**
	 * Holds the definition of the form.
	 *
	 * @var Form
	 */
	protected $form;

	/**
	 * The name of the form. Unique string no spaces.
	 *
	 * @var string
	 */
	public $form_name = '';

	/**
	 * Label of the submission form.
	 *
	 * @var string
	 */
	public $submit_label = '';

	/**
	 * Get things started.
	 */
	public function __construct() {

		if ( ! empty( $this->form_name ) && ! empty( $this->submit_label ) ) {
			$this->setup_form();
		}

	}

	/**
	 * Setup the form object.
	 *
	 * @return void
	 */
	private function setup_form() {
		$this->form = new Form( $this->form_name, $this->get_fields() );
	}

	/**
	 * Get fields definition for the form.
	 *
	 * @return void
	 */
	abstract public function get_fields();

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	abstract public function hook();

	/**
	 * Process the form's submission.
	 *
	 * @return void
	 */
	abstract public function process();

	/**
	 * Get the class name of a given field type.
	 *
	 * @param string $type the defined's field type.
	 * @return string
	 */
	protected function get_field_type_class_name( $type ) {

		$field_type_class = '\PNO\Form\Field\TextField';

		switch ( $type ) {
			case 'password':
				$field_type_class = '\PNO\Form\Field\PasswordField';
				break;
			case 'textarea':
				$field_type_class = '\PNO\Form\Field\TextAreaField';
				break;
			case 'editor':
				$field_type_class = '\PNO\Form\Field\EditorField';
				break;
			case 'email':
				$field_type_class = '\PNO\Form\Field\EmailField';
				break;
			case 'checkbox':
				$field_type_class = '\PNO\Form\Field\CheckboxField';
				break;
			case 'select':
				$field_type_class = '\PNO\Form\Field\DropdownField';
				break;
			case 'multiselect':
				$field_type_class = '\PNO\Form\Field\MultiSelectField';
				break;
			case 'multicheckbox':
				$field_type_class = '\PNO\Form\Field\MultiCheckboxField';
				break;
			case 'number':
				$field_type_class = '\PNO\Form\Field\NumberField';
				break;
			case 'radio':
				$field_type_class = '\PNO\Form\Field\RadioField';
				break;
			case 'url':
				$field_type_class = '\PNO\Form\Field\URLField';
				break;
		}

		/**
		 * Allow developers to define the class name of custom fields if any.
		 *
		 * @param string $field_type_class the class name to return in order to instantiate the field.
		 * @param string $type retrieved field type string.
		 */
		return apply_filters( 'pno_forms_field_class_name', $field_type_class, $type );

	}

}