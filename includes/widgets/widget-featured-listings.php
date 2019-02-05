<?php
/**
 * Handles the featured listings widget.
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
 * Registers the featured listings widget.
 */
class FeaturedListings extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {
		$this->setup(
			'pno_featured_listings_widget',
			esc_html__( '[Posterno] Featured listings', 'posterno' ),
			esc_html__( 'Displays the featured listings.', 'posterno' ),
			array(
				Field::make( 'text', 'title', esc_html__( 'Title', 'posterno' ) ),
				Field::make( 'text', 'number', esc_html__( 'Limit', 'posterno' ) )
					->set_attribute( 'type', 'number' )
					->set_attribute( 'min', '0' )
					->set_help_text( esc_html__( 'Specify the maximum amount of listings to display.', 'posterno' ) ),
				Field::make( 'select', 'layout', esc_html__( 'Layout', 'posterno' ) )->set_options( 'pno_get_listings_layout_available_options' ),
			),
			'pno-widget-featured-listings'
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

		posterno()->templates
			->set_template_data( $instance )
			->get_template_part( 'widgets/featured-listings' );

	}
}
