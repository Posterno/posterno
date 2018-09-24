<?php
/**
 * Main class responsible of handling Posterno's forms.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

use PNO\Form\Field\AbstractField;
use PNO\Form\Layout\AbstractLayout;
use PNO\Form\Layout\DefaultLayout;
use PNO\Form\Sanitizer;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles all Posterno's forms.
 */
class Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Array of form fields
	 *
	 * @var AbstractField[]
	 */
	private $fields;

	/**
	 * The layout handler of the form.
	 *
	 * @var AbstractLayout
	 */
	private $layout;

	/**
	 * Array of validation errors.
	 *
	 * @var array
	 */
	private $errors;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $processing_error;

	/**
	 * Get things started and build the form.
	 *
	 * @param string $name name of the form.
	 * @param array  $fields list of fields for the form.
	 */
	public function __construct( $name, array $fields = array() ) {
		$this
			->set_name( $name )
			->set_fields( $fields )
			->set_layout( new DefaultLayout() );
	}

	/**
	 * Helper method: set the name of the form.
	 *
	 * @param string $name the new name of the form.
	 * @return PNO\Form instance of the form.
	 */
	public function set_name( $name ) {
		$this->name = str_replace( ' ', '_', $name );
		return $this;
	}

	/**
	 * Get the name of the form.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Helper method: manually set fields to the form.
	 *
	 * @param array $fields the list of fields to assign to the form.
	 * @return PNO\Form instance of the form.
	 */
	public function set_fields( array $fields ) {
		foreach ( $fields as $field ) {
			$this->add_field( $field );
		}
		return $this;
	}

	/**
	 * Add a field to the form.
	 *
	 * @param AbstractField $field the field to add.
	 * @return PNO\Form instance of the form.
	 */
	public function add_field( AbstractField $field ) {
		$field->set_parent( $this );
		$this->fields[ $field->get_id() ] = $field;
		return $this;
	}

	/**
	 * Remove a field from the form.
	 *
	 * @param string $name the name of the field to remove.
	 * @return PNO\Form instance of the form.
	 */
	public function remove_field( $name ) {
		if ( isset( $this->fields[ $name ] ) ) {
			unset( $this->fields[ $name ] );
		}
		return $this;
	}

	/**
	 * Retrieve the list of fields assigned to the form.
	 *
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Setup the layout of the form.
	 *
	 * @param AbstractLayout $layout the class that handles the layout of the form.
	 * @return PNO\Form instance of the form.
	 */
	public function set_layout( AbstractLayout $layout ) {
		$this->layout = $layout;
		return $this;
	}

	/**
	 * Bind data to the array of fields.
	 *
	 * @param array $data the data binding to the form.
	 * @return PNO\Form instance of the form.
	 */
	public function bind( array $data = array() ) {
		foreach ( $this->fields as $field ) {
			if ( isset( $data[ $field->get_id() ] ) ) {
				$field->bind( $data[ $field->get_id() ] );
			} else {
				$field->bind( null );
			}
		}
		return $this;
	}

	/**
	 * Verify if the submitted for is valid.
	 *
	 * @return boolean
	 */
	public function is_valid() {
		foreach ( $this->fields as $field ) {
			$field  = Sanitizer::sanitize_field( $field );
			$result = \PNO\Form\Validator::validate_field( $field );
			if ( ! $result->valid ) {
				$this->errors[ $field->get_id() ] = $result->trace;
			}
		}
		empty( $this->processing_error );
		return empty( $this->errors );
	}

	/**
	 * Verify if the form has errors.
	 *
	 * @return boolean
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Retrieve the list of errors for the form.
	 *
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Determine if there's a processing error within the form.
	 *
	 * @return boolean
	 */
	public function has_processing_error() {
		return ! empty( $this->processing_error );
	}

	/**
	 * Retrieve the processing error assigned to the form.
	 *
	 * @return string
	 */
	public function get_processing_error() {
		return $this->processing_error;
	}

	/**
	 * Set a processing error to the form.
	 *
	 * @param string $error
	 * @return void
	 */
	public function set_processing_error( $error ) {
		$this->processing_error = $error;
	}

	/**
	 * Retrieve the data submitted through the form.
	 *
	 * @return array
	 */
	public function get_data() {
		$result = array();
		foreach ( $this->fields as $field ) {
			$result[ $field->get_id() ] = $field->get_value();
		}
		return $result;
	}

	/**
	 * Render the form.
	 *
	 * @return void
	 */
	public function render() {
		foreach ( $this->fields as $name => $instance ) {
			$this->render_field( $name );
		}
	}

	/**
	 * Individually render fields given a name.
	 *
	 * @param string $name the name of the field to render.
	 * @return void
	 */
	public function render_field( $name ) {
		if ( isset( $this->fields[ $name ] ) ) {
			echo $this->layout->render_field( $this->fields[ $name ] ); //phpcs:ignore
		}
	}

}
