<?php
/**
 * Handles the listing location map widget for the single listing page.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace PNO\Widget;

use Carbon_Fields\Widget;
use Carbon_Fields\Field;

/**
 * Registers the single listing location map widget.
 */
class ListingLocationMap extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {
		$this->setup(
			'pno_listing_location_map_widget',
			esc_html__( '[Posterno] Listing location map', 'posterno' ),
			esc_html__( 'Displays the current listing\'s location on the map.', 'posterno' ),
			array(
				Field::make( 'text', 'title', esc_html__( 'Title', 'posterno' ) )->set_default_value( esc_html__( 'Location', 'posterno' ) ),
			),
			'pno-widget-listing-location-map'
		);
	}

	/**
	 * Display the widget on the frontend.
	 *
	 * @param array $args all the sidebar settings.
	 * @param array $instance all the widget settings.
	 * @return void
	 */
	public function front_end( $args, $instance ) {

		echo $args['before_title'] . wp_kses_post( $instance['title'] ) . $args['after_title']; //phpcs:ignore

		if ( ! is_singular( 'listings' ) ) {
			posterno()->templates
				->set_template_data(
					[
						'type'    => 'danger',
						'message' => pno_get_widget_singular_restriction_message(),
					]
				)
				->get_template_part( 'message' );
				return;
		}

		posterno()->templates
			->set_template_data( $args )
			->get_template_part( 'widgets/listing-location-map' );

	}
}
