<?php
/**
 * Handles the listing video widget for the single listing page.
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
 * Registers the single listing video widget.
 */
class ListingVideo extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {
		$this->setup(
			'pno_listing_video_widget',
			esc_html__( '[Posterno] Listing video' ),
			esc_html__( 'Displays the current listing\'s attached video.' ),
			array(
				Field::make( 'text', 'title', esc_html__( 'Title' ) ),
			),
			'pno-widget-listing-video'
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
			->set_template_data( $instance )
			->get_template_part( 'widgets/listing-video' );

	}
}
