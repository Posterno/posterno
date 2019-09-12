<?php
/**
 * Handles the listing author widget for the single listing page.
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
 * Registers the single listing author widget.
 */
class ListingAuthor extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {

		$labels = array(
			'plural_name'   => esc_html__( 'fields', 'posterno' ),
			'singular_name' => esc_html__( 'field', 'posterno' ),
		);

		$this->setup(
			'pno_listing_author_widget',
			esc_html__( '[Posterno] Listing Author', 'posterno' ),
			esc_html__( 'Displays the current listing\'s author and his details.', 'posterno' ),
			array(
				Field::make( 'text', 'title', esc_html__( 'Title', 'posterno' ) ),
				Field::make( 'checkbox', 'display_member_since', esc_html__( 'Display registration date', 'posterno' ) )->set_default_value( true ),
				Field::make( 'complex', 'additional_fields', esc_html__( 'Display additional fields', 'posterno' ) )
					->add_fields(
						array(
							Field::make( 'select', 'field_id', esc_html__( 'Select profile field', 'posterno' ) )->set_options( 'pno_get_profile_fields_for_widget_association' ),
						)
					)
					->setup_labels( $labels ),
			),
			'pno-widget-listing-author'
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
			->get_template_part( 'widgets/listing-author' );

	}
}
