<?php
/**
 * Handles the listing details widget for the single listing page.
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
 * Registers the single listing details widget.
 */
class ListingDetails extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {

		$labels = array(
			'plural_name'   => esc_html__( 'fields' ),
			'singular_name' => esc_html__( 'field' ),
		);

		$this->setup(
			'pno_listing_details_widget',
			esc_html__( '[Posterno] Listing details' ),
			esc_html__( 'Displays the current listing\'s details and custom fields.' ),
			array(
				Field::make( 'text', 'title', esc_html__( 'Title' ) ),
				Field::make( 'complex', 'additional_fields', esc_html__( 'Display additional fields' ) )
					->add_fields(
						array(
							Field::make( 'select', 'field_id', esc_html__( 'Select field' ) )->set_options( 'pno_get_listings_fields_for_widget_association' ),
						)
					)
					->setup_labels( $labels ),
			),
			'pno-widget-listing-details'
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
			->get_template_part( 'widgets/listing-details' );

	}
}
