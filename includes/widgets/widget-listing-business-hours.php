<?php
/**
 * Handles the listing business hours widget for the single listing page.
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
 * Registers the single listing video widget.
 */
class BusinessHours extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {
		$this->setup(
			'pno_listing_business_hours_widget',
			esc_html__( '[Posterno] Listing business hours', 'posterno' ),
			esc_html__( 'Displays the current listing\'s business hours.', 'posterno' ),
			array(
				Field::make( 'text', 'title', esc_html__( 'Title', 'posterno' ) ),
			),
			'pno-widget-business-hours'
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
			->set_template_data(
				[
					'listing_id' => get_queried_object_id(),
				]
			)
			->get_template_part( 'listings/business-hours' );

	}
}
