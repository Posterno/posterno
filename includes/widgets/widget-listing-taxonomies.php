<?php
/**
 * Handles the listing taxonomies widget for the single listing page.
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
class ListingTaxonomies extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {
		$this->setup(
			'pno_listing_taxonomies_widget',
			esc_html__( '[Posterno] Listing taxonomies terms' ),
			esc_html__( 'Displays the current listing\'s attached taxonomies terms.' ),
			array(
				Field::make( 'text', 'title', esc_html__( 'Title' ) ),
				Field::make( 'select', 'taxonomy', esc_html__( 'Taxonomy to display' ) )->set_options( 'pno_get_registered_listings_taxonomies' ),
			),
			'pno-widget-listing-taxonomies'
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
			->get_template_part( 'widgets/listing-taxonomies' );

	}
}
