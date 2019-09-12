<?php
/**
 * Handles the recent listings widget.
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
 * Registers the recent listings widget.
 */
class RecentListings extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {
		$this->setup(
			'pno_recent_listings_widget',
			esc_html__( '[Posterno] Recent listings', 'posterno' ),
			esc_html__( 'Displays the most recent listings.', 'posterno' ),
			array(
				Field::make( 'text', 'title', esc_html__( 'Title', 'posterno' ) ),
				Field::make( 'text', 'number', esc_html__( 'Limit', 'posterno' ) )
					->set_attribute( 'type', 'number' )
					->set_attribute( 'min', '0' )
					->set_help_text( esc_html__( 'Specify the maximum amount of listings to display.', 'posterno' ) ),
				Field::make( 'select', 'layout', esc_html__( 'Layout', 'posterno' ) )->set_options( 'pno_get_listings_layout_available_options' ),
			),
			'pno-widget-recent-listings'
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
			->get_template_part( 'widgets/recent-listings' );

	}
}
