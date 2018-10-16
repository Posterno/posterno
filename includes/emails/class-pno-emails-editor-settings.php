<?php
/**
 * Defines the settings for the emails editor.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class that handles registration of the settings for the emails editor.
 */
class PNO_Emails_Editor_Settings {

	/**
	 * Hook into WordPress and register custom fields for the emails editor.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'carbon_fields_register_fields', [ $this, 'register_settings' ] );
	}

	/**
	 * Register settings for the emails editor.
	 *
	 * @return void
	 */
	public function register_settings() {

		Container::make( 'post_meta', esc_html__( 'Available email template tags:' ) )
			->where( 'post_type', '=', 'pno_emails' )
			->set_context( 'carbon_fields_after_title' )
			->add_fields(
				array(
					Field::make( 'html', 'pno_email_tags_list' )
						->set_html( pno_get_emails_tags_list() ),
				)
			);

		Container::make( 'post_meta', esc_html__( 'Situations' ) )
			->where( 'post_type', '=', 'pno_emails' )
			->set_context( 'side' )
			->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'set', 'crb_product_features', '' )
						->set_datastore( new PNO\Datastores\EmailSituations() )
						->set_help_text( 'Choose when this email will be sent.' )
						->add_options( 'pno_get_emails_situations' ),
				)
			);

	}

}

( new PNO_Emails_Editor_Settings() )->init();
