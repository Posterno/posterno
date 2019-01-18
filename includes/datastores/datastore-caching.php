<?php
/**
 * Custom datastore for carbon fields metadata to leverage WordPress caching.
 *
 * @package     posterno
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Pressmodo LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Datastores;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Datastore\Post_Meta_Datastore;
use Carbon_Fields\Datastore\Term_Meta_Datastore;
use Carbon_Fields\Datastore\User_Meta_Datastore;
use Carbon_Fields\Field\Field;
use Carbon_Fields\Toolset\Key_Toolset;

/**
 * Responsible of loading metadata.
 */
trait EagerLoadingMetaDatastore {

	/**
	 * Retrieve storage array through native metadatas.
	 *
	 * @param Field $field field object.
	 * @param array $storage_key_patterns previous patterns.
	 * @return array
	 */
	protected function get_storage_array( Field $field, $storage_key_patterns ) {

		$storage = [];
		$meta    = get_metadata( $this->get_meta_type(), $this->get_object_id() );
		if ( ! $meta ) {
			return $storage;
		}

		foreach ( $storage_key_patterns as $storage_key => $type ) {
			switch ( $type ) {
				case Key_Toolset::PATTERN_COMPARISON_EQUAL:
					if ( isset( $meta[ $storage_key ] ) ) {
						$obj        = new \stdClass();
						$obj->key   = $storage_key;
						$obj->value = $meta[ $storage_key ][0];
						$storage[]  = $obj;
					}
					break;
				case Key_Toolset::PATTERN_COMPARISON_STARTS_WITH:
					foreach ( $meta as $key => $value ) {
						if ( strpos( $key, $storage_key ) === 0 ) {
							$obj        = new \stdClass();
							$obj->key   = $key;
							$obj->value = $meta[ $key ][0];
							$storage[]  = $obj;
						}
					}
					break;
				default:
					throw new \LogicException( "Unknown storage key pattern type: {$type}" );
					break;
			}
		}

		$storage = apply_filters( 'carbon_fields_datastore_storage_array', $storage, $this, $storage_key_patterns );

		return $storage;
	}
}

/**
 * Class that hooks into carbon fields storage system.
 */
final class EagerLoadingPostMetaDatastore extends Post_Meta_Datastore {
	use EagerLoadingMetaDatastore;
}

/**
 * Class that hooks into the Terms meta storage system.
 */
final class EagerLoadingTermMetaDatastore extends Term_Meta_Datastore {
	use EagerLoadingMetaDatastore;
}

/**
 * Class that hooks into the User meta storage system.
 */
final class EagerLoadingUserMetaDatastore extends User_Meta_Datastore {
	use EagerLoadingMetaDatastore;
}

/**
 * Add caching to all containers that make use of the post meta datastore.
 */
add_action(
	'carbon_fields_fields_registered', function () {
		$repo = Carbon_Fields::resolve( 'container_repository' );
		foreach ( $repo->get_containers() as $container ) {
			$datastore = $container->get_datastore();
			if ( $datastore instanceof Post_Meta_Datastore ) {
				$container->set_datastore( new EagerLoadingPostMetaDatastore() );
			} elseif ( $datastore instanceof Term_Meta_Datastore ) {
				$container->set_datastore( new EagerLoadingTermMetaDatastore() );
			} elseif ( $datastore instanceof User_Meta_Datastore ) {
				$container->set_datastore( new EagerLoadingUserMetaDatastore() );
			}
		}
	}
);
