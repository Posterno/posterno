<?php
/**
 * Defines a representation of a Posterno listing field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Field;

use PNO\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing field of Posterno's forms.
 */
class Listing extends Field {

	/**
	 * The type of data the field will'be storing into the database.
	 *
	 * Available types: post_meta, user_meta.
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $object_type = 'post_meta';

	/**
	 * The prefix used by Carbon Fields to store field's settings.
	 *
	 * @var string
	 */
	protected $field_setting_prefix = 'listing_field_';

	/**
	 * The post type where these type of fields are stored.
	 *
	 * @var string
	 */
	protected $post_type = 'pno_listings_fields';

	/**
	 * Query the database for all the settings belonging to the field.
	 *
	 * @param string $post_id the id of the post (field) for which we're going to query.
	 * @return void
	 */
	public function populate_from_post_id( $post_id ) {

		$this->post_id = $post_id;
		$this->name    = get_the_title( $post_id );

		$settings = [];

		$field       = new \PNO\Database\Queries\Listing_Fields();
		$found_field = $field->get_item_by( 'post_id', $post_id );

		if ( $found_field instanceof $this ) {
			$this->id = $found_field->get_id();
			$settings = $found_field->get_settings();
		}

		if ( is_array( $settings ) ) {
			$this->settings = $settings;

			foreach ( $settings as $setting => $value ) {
				$setting = $this->remove_prefix_from_setting_id( '_listing_field_', $setting );
				switch ( $setting ) {
					case 'is_required':
						$this->required = $value;
						break;
					case 'is_default':
						$this->object_meta_key = $value;
						break;
					default:
						$this->{$setting} = $value;
						break;
				}
			}
		}

		if ( empty( $this->get_label() ) ) {
			$this->label = $this->get_name();
		}

		$this->can_delete = pno_is_default_field( $this->object_meta_key ) ? false : true;

		$types = pno_get_registered_field_types();

	}

}
