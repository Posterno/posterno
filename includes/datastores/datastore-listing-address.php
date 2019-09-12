<?php
/**
 * Custom datastore for listings address metadata.
 *
 * @package     posterno
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Datastores;

use Carbon_Fields\Field\Field;
use Carbon_Fields\Datastore\Datastore;
use Carbon_Fields\Datastore\Meta_Datastore;
use Carbon_Fields\Datastore\Post_Meta_Datastore;
use Carbon_Fields\Toolset\Key_Toolset;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manage storage of the address and coordinates of listings.
 */
class ListingAddress extends Post_Meta_Datastore {

	/**
	 * Get the defined key for the field.
	 *
	 * @param Field $field field object.
	 * @return string
	 */
	protected function get_key_for_field( Field $field ) {
		$key = '_' . $field->get_base_name();
		return $key;
	}

	/**
	 * Load stored coordinates from the database.
	 *
	 * @param Field $field the field to load.
	 * @return array
	 */
	public function load( Field $field ) {

		$key = $this->get_key_for_field( $field );

		$address     = get_post_meta( $this->get_object_id(), '_listing_location_address', true );
		$coordinates = get_post_meta( $this->get_object_id(), '_listing_location_coordinates', true );
		$lat         = get_post_meta( $this->get_object_id(), '_listing_location_lat', true );
		$lng         = get_post_meta( $this->get_object_id(), '_listing_location_lng', true );

		// If nothing is found, prepare some fake coordinates so that the map isn't blank in the admin panel.
		if ( empty( $lat ) || empty( $lng ) ) {
			$lat         = pno_get_option( 'map_starting_lat', '40.7484405' );
			$lng         = pno_get_option( 'map_starting_lng', '-73.9944191' );
			$coordinates = "{$lat},{$lng}";
			$address     = '';
		}

		$full_value = [];

		$full_value[] = [
			'value'   => $coordinates,
			'lat'     => floatval( $lat ),
			'lng'     => floatval( $lng ),
			'address' => $address,
			'zoom'    => 10,
		];

		return $full_value;

	}

	/**
	 * Save coordinates and address into the database.
	 *
	 * @param Field $field the field to save.
	 * @return void
	 */
	public function save( Field $field ) {

		if ( ! empty( $field->get_hierarchy() ) ) {
			return;
		}

		$key   = $this->get_key_for_field( $field );
		$value = $field->get_formatted_value();

		$address     = isset( $value['address'] ) ? sanitize_text_field( $value['address'] ) : false;
		$coordinates = isset( $value['value'] ) ? sanitize_text_field( $value['value'] ) : false;
		$lat         = isset( $value['lat'] ) ? sanitize_text_field( $value['lat'] ) : false;
		$lng         = isset( $value['lng'] ) ? sanitize_text_field( $value['lng'] ) : false;

		if ( $address ) {
			update_post_meta( $this->get_object_id(), '_listing_location_address', $address );
		}
		if ( $coordinates ) {
			update_post_meta( $this->get_object_id(), '_listing_location_coordinates', $coordinates );
		}
		if ( $lat ) {
			update_post_meta( $this->get_object_id(), '_listing_location_lat', $lat );
		}
		if ( $lng ) {
			update_post_meta( $this->get_object_id(), '_listing_location_lng', $lng );
		}

	}

	/**
	 * Delete coordinates or address when they're not needed.
	 *
	 * @param Field $field the field to delete.
	 * @return void
	 */
	public function delete( Field $field ) {

		if ( ! empty( $field->get_hierarchy() ) ) {
			return;
		}

		$key   = $this->get_key_for_field( $field );
		$value = $field->get_formatted_value();

		$address     = isset( $value['address'] ) ? sanitize_text_field( $value['address'] ) : false;
		$coordinates = isset( $value['value'] ) ? sanitize_text_field( $value['value'] ) : false;
		$lat         = isset( $value['lat'] ) ? sanitize_text_field( $value['lat'] ) : false;
		$lng         = isset( $value['lng'] ) ? sanitize_text_field( $value['lng'] ) : false;

		if ( ! $address ) {
			delete_post_meta( $this->get_object_id(), '_listing_location_address' );
		}
		if ( ! $coordinates ) {
			delete_post_meta( $this->get_object_id(), '_listing_location_coordinates' );
		}
		if ( ! $lat ) {
			delete_post_meta( $this->get_object_id(), '_listing_location_lat' );
		}
		if ( ! $lng ) {
			delete_post_meta( $this->get_object_id(), '_listing_location_lng' );
		}

	}

}
