<?php
/**
 * Defines a representation of a Posterno profile field.
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
 * Profile field representation of posterno's fields.
 */
class Profile extends Field {

	/**
	 * The type of data the field will'be storing into the database.
	 *
	 * Available types: post_meta, user_meta.
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $object_type = 'user_meta';

	/**
	 * The prefix used by Carbon Fields to store field's settings.
	 *
	 * @var string
	 */
	protected $field_setting_prefix = 'profile_field_';

	/**
	 * The post type where these type of fields are stored.
	 *
	 * @var string
	 */
	protected $post_type = 'pno_users_fields';

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

		$field       = new \PNO\Database\Queries\Profile_Fields();
		$found_field = $field->get_item_by( 'post_id', $post_id );

		if ( $found_field instanceof $this ) {
			$this->id = $found_field->get_id();
			$settings = $found_field->get_settings();
		}

		if ( is_array( $settings ) ) {
			$this->settings = $settings;

			foreach ( $settings as $setting => $value ) {
				$setting = $this->remove_prefix_from_setting_id( '_profile_field_', $setting );
				switch ( $setting ) {
					case 'is_required':
						$this->required = $value;
						break;
					case 'meta_key':
						$this->object_meta_key = $value;
						break;
					case 'is_read_only':
						$this->readonly = $value;
						break;
					case 'is_admin_only':
						$this->admin_only = $value;
						break;
					case 'selectable_options':
						$this->options = pno_parse_selectable_options( $value );
						break;
					case 'file_max_size':
						$this->maxsize = $value;
						break;
					default:
						$this->{$setting} = $value;
						break;
				}
			}
		}

		$types               = pno_get_registered_field_types();
		$this->type_nicename = isset( $types[ $this->type ] ) ? $types[ $this->type ] : false;

		if ( empty( $this->get_label() ) ) {
			$this->label = $this->get_name();
		}

		if ( in_array( $this->type, pno_get_multi_options_field_types() ) ) {
			$this->multiple = true;
		}

		$this->can_delete = pno_is_default_field( $this->object_meta_key ) ? false : true;

	}

	/**
	 * Create a new field and save it into the database.
	 *
	 * @param array $args list of arguments to create a new field.
	 * @throws InvalidArgumentException When missing arguments.
	 * @return string
	 */
	public function create( $args = [] ) {

		if ( ! isset( $args['name'] ) || empty( $args['name'] ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t find property %s' ), 'name' ) );
		}

		if ( empty( $args['meta'] ) ) {
			$meta         = sanitize_title( $args['name'] );
			$meta         = str_replace( '-', '_', $meta );
			$args['meta'] = $meta;
		}

		$field_args = [
			'post_type'   => $this->get_post_type(),
			'post_title'  => $args['name'],
			'post_status' => 'publish',
		];

		$field_id = wp_insert_post( $field_args );

		if ( ! is_wp_error( $field_id ) ) {

			$field = new \PNO\Database\Queries\Profile_Fields();
			$field->add_item( [ 'post_id' => $field_id ] );

			carbon_set_post_meta( $field_id, $this->get_field_setting_prefix() . 'profile_field_id', $args['profile_field_id'] );

			if ( isset( $args['priority'] ) && ! empty( $args['priority'] ) ) {
				carbon_set_post_meta( $field_id, $this->get_field_setting_prefix() . 'priority', $args['priority'] );
			}

			if ( isset( $args['meta'] ) && ! empty( $args['meta'] ) ) {
				carbon_set_post_meta( $field_id, $this->get_field_setting_prefix() . 'meta_key', $args['meta'] );
			}

			if ( isset( $args['type'] ) && ! empty( $args['type'] ) ) {
				carbon_set_post_meta( $field_id, $this->get_field_setting_prefix() . 'type', $args['type'] );
			}

			return $field_id;

		}

	}

	/**
	 * Delete a field from the database and delete it's associated settings too.
	 *
	 * @return void
	 */
	public function delete() {

		wp_delete_post( $this->get_post_id(), true );

		$field = new \PNO\Database\Queries\Profile_Fields();

		$found_field = $field->get_item_by( 'post_id', $this->get_post_id() );

		$field->delete_item( $found_field->get_id() );

		// Delete registration field automatically if found attached.
		$reg_field_query             = new \PNO\Database\Queries\Registration_Fields();
		$attached_registration_field = $reg_field_query->get_item_by( 'profile_field_id', $this->get_post_id() );
		$registration_field          = new \PNO\Field\Registration( $attached_registration_field->get_post_id() );

		if ( $registration_field->can_delete() ) {
			$registration_field->delete();
		}

	}

}
