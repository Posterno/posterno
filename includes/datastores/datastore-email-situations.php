<?php
/**
 * Custom datastore for the email situation checkbox.
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
 * On save, assign the email a "type" taxonomy object instead of saving it as post meta.
 */
class EmailSituations extends Post_Meta_Datastore {

	/**
	 * Load the selected type for the email.
	 *
	 * @param Field $field the field we're working with.
	 * @return string|int
	 */
	public function load( Field $field ) {

		$terms = wp_get_post_terms( $this->get_object_id(), 'pno-email-type', [ 'fields' => 'ids' ] );

		$stored_types = [];

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term_id ) {
				$stored_types[] = absint( $term_id );
			}
		}

		return $stored_types;

	}

	/**
	 * Assign the selected email type taxonomy object id to the email.
	 *
	 * @param Field $field the custom field.
	 * @return void
	 */
	public function save( Field $field ) {

		if ( ! empty( $field->get_hierarchy() ) ) {
			return;
		}

		$value = $field->get_formatted_value();

		if ( ! empty( $value ) && is_array( $value ) ) {

			wp_delete_object_term_relationships( $this->get_object_id(), 'pno-email-type' );

			foreach ( $value as $type_id ) {
				wp_set_object_terms( absint( $this->get_object_id() ), absint( $type_id ), 'pno-email-type', true );
			}

		} else {

			wp_delete_object_term_relationships( $this->get_object_id(), 'pno-email-type' );

		}

	}

}
