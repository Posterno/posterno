<?php
/**
 * Representation of a listing location field.
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
 * The class responsible of handling listings location within a PNO\Form.
 */
class ListingLocationField extends AbstractGroup {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		parent::init();
		$this->set_type( 'listing-location' );
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
			$value           = json_decode( wp_unslash( $value ) );
			$redefined_value = [];
			if ( isset( $value->coordinates ) ) {
				$redefined_value = [
					'lat' => $value->coordinates->lat,
					'lng' => $value->coordinates->lng,
				];
			}
			return $this->set_value( wp_json_encode( $redefined_value ) );
		}
		return $this->set_value( array() );
	}

}
