<?php
/**
 * Defines a representation of a Posterno registration field.
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
 * Registration field of Posterno's forms.
 */
class Registration extends Field {

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
	protected $field_setting_prefix = 'registration_field_';

	/**
	 * The post type where these type of fields are stored.
	 *
	 * @var string
	 */
	protected $post_type = 'pno_signup_fields';

	/**
	 * Registration fields are associated to a profile field when
	 * they're not default fields.
	 *
	 * @var boolean|string
	 */
	protected $profile_field_id = false;

	/**
	 * Retrieve the profile field post id attached to the registration field.
	 *
	 * @return string
	 */
	public function get_profile_field_id() {
		return $this->profile_field_id;
	}

	/**
	 * Query the database for all the settings belonging to the field.
	 *
	 * @param string $post_id the id of the post (field) for which we're going to query.
	 * @return void
	 */
	public function populate_from_post_id( $post_id ) {

		$this->post_id = $post_id;
		$this->set_title( absint( $post_id ) );

		$settings = [];

		$field       = new \PNO\Database\Queries\Registration_Fields();
		$found_field = $field->get_item_by( 'post_id', $post_id );

		if ( $found_field instanceof $this ) {

			$this->id = $found_field->get_id();
			$settings = $found_field->get_settings();

			$this->parse_settings( $settings );

		}

	}

	/**
	 * Parse all settings assigned to the field and complete setup of the field's object.
	 *
	 * @param array $settings settings to parse.
	 * @return void
	 */
	public function parse_settings( $settings ) {

		if ( is_array( $settings ) ) {
			$this->settings = $settings;

			foreach ( $settings as $setting => $value ) {
				$setting = $this->remove_prefix_from_setting_id( '_registration_field_', $setting );
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

		if ( ! $this->can_delete() ) {

			$type = 'text';

			switch ( $this->get_object_meta_key() ) {
				case 'password':
					$type = 'password';
					break;
				case 'email':
					$type = 'email';
					break;
			}

			$this->type          = $type;
			$this->type_nicename = isset( $types[ $this->type ] ) ? $types[ $this->type ] : false;

			// Force requirement for the email field.
			if ( $this->get_object_meta_key() === 'email' ) {
				$this->required = true;
			}
		}

		// Attach profile field to the registration field if not a default field.
		if ( ! empty( $this->profile_field_id ) ) {

			$profile_field = new Profile( $this->profile_field_id );

			if ( $profile_field instanceof Profile ) {
				$this->type            = $profile_field->get_type();
				$this->type_nicename   = isset( $types[ $profile_field->get_type() ] ) ? $types[ $profile_field->get_type() ] : false;
				$this->object_meta_key = $profile_field->get_object_meta_key();

				if ( is_array( $profile_field->get_options() ) && ! empty( $profile_field->get_options() ) ) {
					$this->options = $profile_field->get_options();
				}

				if ( in_array( $profile_field->get_type(), pno_get_multi_options_field_types() ) ) {
					$this->multiple = true;
				}
			}
		}

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

		if ( ! isset( $args['profile_field_id'] ) || empty( $args['profile_field_id'] ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t find property %s' ), 'profile_field_id' ) );
		}

		$field_args = [
			'post_type'   => $this->get_post_type(),
			'post_title'  => $args['name'],
			'post_status' => 'publish',
		];

		$field_id = wp_insert_post( $field_args );

		if ( ! is_wp_error( $field_id ) ) {

			$field = new \PNO\Database\Queries\Registration_Fields();
			$field->add_item(
				[
					'post_id'          => $field_id,
					'profile_field_id' => isset( $args['profile_field_id'] ) ? absint( $args['profile_field_id'] ) : false,
				]
			);

			if ( isset( $args['profile_field_id'] ) ) {
				carbon_set_post_meta( $field_id, $this->get_field_setting_prefix() . 'profile_field_id', $args['profile_field_id'] );
			}

			if ( isset( $args['priority'] ) && ! empty( $args['priority'] ) ) {
				carbon_set_post_meta( $field_id, $this->get_field_setting_prefix() . 'priority', $args['priority'] );
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

		$field = new \PNO\Database\Queries\Registration_Fields();

		$found_field = $field->get_item_by( 'post_id', $this->get_post_id() );

		$field->delete_item( $found_field->get_id() );

	}

}
