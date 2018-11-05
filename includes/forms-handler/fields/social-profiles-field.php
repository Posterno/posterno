<?php
/**
 * Representation of a social profiles selector field.
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
 * The class responsible of handling dropdown fields within a PNO\Form.
 */
class SocialProfilesField extends AbstractGroup {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		parent::init();
		$this->set_type( 'social-profiles' );
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
			if ( is_array( $value ) && ! empty( $value ) ) {
				foreach ( $value as $social_profile ) {
					if ( isset( $social_profile->social ) && isset( $social_profile->url ) ) {
						$redefined_value[] = [
							'social_id'  => $social_profile->social,
							'social_url' => $social_profile->url,
						];
					}
				}
			}
			$value = $redefined_value;
			return $this->set_value( wp_json_encode( $value ) );
		}
		return $this->set_value( array() );
	}

	/**
	 * Verify if the choice exists.
	 *
	 * @param string $choice the choice to check.
	 * @return boolean
	 */
	private function exists( $choice ) {
		return array_key_exists( $choice, $this->choices );
	}

}
