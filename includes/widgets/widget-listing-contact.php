<?php
/**
 * Handles the listing video widget for the single listing page.
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
class ListingContact extends Widget {

	/**
	 * Build the widget and it's settings.
	 */
	public function __construct() {
		$this->setup(
			'pno_listing_contact_widget',
			esc_html__( '[Posterno] Listing contact form', 'posterno' ),
			esc_html__( 'Displays a contact form through which members can send emails to the author of the listing.', 'posterno' ),
			$this->get_widget_settings(),
			'pno-widget-listing-contact'
		);
	}

	/**
	 * Get settings list for the widget.
	 *
	 * @return array
	 */
	public function get_widget_settings() {

		$settings = [];

		$settings[] = Field::make( 'text', 'title', esc_html__( 'Title', 'posterno' ) );
		$settings[] = Field::make( 'checkbox', 'require_login', esc_html__( 'Login required', 'posterno' ) )->set_help_text( esc_html__( 'Require visitors to be logged in to contact the author.', 'posterno' ) );

		/**
		 * Filter: allow developers to register or deregister settings for the listing contact form widget.
		 *
		 * @param array $settings carbon fields settings list.
		 * @return $array
		 */
		return apply_filters( 'pno_listing_contact_widget_settings', $settings );

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
			->get_template_part( 'widgets/listing-contact' );

	}
}
