<?php
/**
 * Handles registration of debug data dedicated to Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Field\Checkbox_Field;

/**
 * Register custom debug data for the site health manager.
 */
class DebugData {

	/**
	 * Hook into WP.
	 *
	 * @return void
	 */
	public function init() {

		add_filter( 'debug_information', [ $this, 'register_debug_data' ] );

	}

	/**
	 * Register data.
	 *
	 * @param array $data existing data.
	 * @return array
	 */
	public function register_debug_data( $data ) {

		$data['posterno'] = array(
			'label'  => esc_html__( 'Posterno', 'posterno' ),
			'fields' => [
				'taxonomies' => [
					'label' => esc_html__( 'Registered taxonomies', 'posterno' ),
					'value' => implode( ', ', $this->get_taxonomies() ),
				],
				'templates'  => [
					'label' => esc_html__( 'Replaced templates', 'posterno' ),
					'value' => implode( ', ', $this->get_templates() ),
				],
			],
		);

		$options = $this->get_options();

		foreach ( $options as $option ) {

			$data['posterno']['fields'][ $option['id'] ] = [
				'label' => $option['label'],
				'value' => is_array( $option['value'] ) ? wp_json_encode( $option['value'] ) : $option['value'],
			];

		}

		return $data;

	}

	/**
	 * Get taxonomies for the listings post type.
	 *
	 * @return array
	 */
	private function get_taxonomies() {

		$taxonomies = get_object_taxonomies( 'listings', 'objects' );

		$list = [];

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $id => $tax ) {
				$list[] = $id;
			}
		}

		return $list;

	}

	/**
	 * Get replace template files list.
	 *
	 * @return array
	 */
	private function get_templates() {

		$templates = \PNO\Admin\TemplatesCheck::get_replaced_template_files();

		return $templates;

	}

	/**
	 * Get a list of Posterno's options and their values.
	 *
	 * @return void
	 */
	private function get_options() {

		$list = [];

		$repo = Carbon_Fields::resolve( 'container_repository' );

		foreach ( $repo->get_containers() as $container ) {

			if ( pno_starts_with( $container->get_id(), 'carbon_fields_container_posterno_settings' ) ) {

				$fields = $container->get_fields();

				foreach ( $fields as $field ) {

					$value = pno_get_option( substr( $field->get_name(), 1 ) );

					if ( $value ) {

						$list[] = [
							'id'    => substr( $field->get_name(), 1 ),
							'value' => $value,
							'label' => $field instanceof Checkbox_Field ? substr( $field->get_name(), 1 ) : $field->get_label(),
						];

					}
				}
			}
		}

		return $list;

	}

}

( new DebugData() )->init();
