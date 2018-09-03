<?php
/**
 * Custom datastore for listings metadata.
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
use Carbon_Fields\Toolset\Key_Toolset;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manage storage of details for listings details.
 */
class Listings extends Post_Meta_Datastore {

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
	 * Save the field value(s)
	 *
	 * @param Field $field The field to save.
	 */
	public function save( Field $field ) {

		return false;

	}

}
