<?php
/**
 * Custom datastore for listing type custom field.
 *
 * @package     posterno
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Datastores;

use Carbon_Fields\Field\Field;
use Carbon_Fields\Datastore\Datastore;
use Carbon_Fields\Datastore\Meta_Datastore;
use Carbon_Fields\Datastore\Post_Meta_Datastore;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * On save, assign the listing a "type" taxonomy object instead of saving it as post meta.
 */
class ListingType extends Post_Meta_Datastore {

	/**
	 * Load the selected type for the listing.
	 *
	 * @param Field $field the field we're working with.
	 * @return string|int
	 */
	public function load( Field $field ) {

		$listing_id = $this->get_object_id();

		$terms = wp_get_post_terms( $listing_id, 'listings-types', [ 'fields' => 'ids' ] );

		if ( ! empty( $terms ) && is_array( $terms ) ) {
			return absint( $terms[0] );
		} else {
			return null;
		}

	}

	/**
	 * Assign the selected listing type taxonomy object id to the listing.
	 *
	 * @param Field $field the custom field.
	 * @return void
	 */
	public function save( Field $field ) {

		if ( ! empty( $field->get_hierarchy() ) ) {
			return;
		}

		$value = $field->get_formatted_value();

		pno_assign_type_to_listing( $this->get_object_id(), $value );

	}

}
