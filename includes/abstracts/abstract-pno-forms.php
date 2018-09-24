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

}
