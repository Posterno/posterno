<?php
/**
 * Handles the listing location map widget for the single listing page.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
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
			esc_html__( '[Posterno] Single listing location map' ),
			esc_html__( 'Displays the location of the listing on the map. Works only on the single listing page.' ),
			array(
				Field::make( 'text', 'title', 'Title' )->set_default_value( 'Hello World!' ),
				Field::make( 'textarea', 'content', 'Content' )->set_default_value( 'Lorem Ipsum dolor sit amet' ),
			)
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
		echo $args['before_title'] . wp_kses( $instance['title'] ) . $args['after_title']; //phpcs:ignore
	}
}
