<?php
/**
 * Representation of a listing tags selector field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class responsible of handling the listings tags field within a PNO\Form.
 */
class ListingTagsField extends AbstractGroup {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		parent::init();
		$this->set_type( 'listing-tags' );
		return $this->set_value( $this->get_option( 'value', [] ) );
	}

	/**
	 * Bind the value of the field.
	 *
	 * @param string $value the value of the field.
	 * @return $this the current object.
	 */
	public function bind( $value ) {
		if ( $value ) {
			return $this->set_value( maybe_unserialize( $value ) );
		}
		return $this->set_value( array() );
	}

}
