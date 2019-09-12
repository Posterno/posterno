<?php
/**
 * Defines the settings for the emails editor.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
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

		$datastore = new PNO\Datastores\DataCompressor();
		$datastore->set_storage_metakey( 'email_settings' );

		Container::make( 'post_meta', esc_html__( 'Notification settings', 'posterno' ) )
			->set_context( 'carbon_fields_after_title' )
			->set_datastore( $datastore )
			->where( 'post_type', '=', 'pno_emails' )
			->add_fields( array(
				Field::make( 'text', 'heading', esc_html__( 'Email heading title', 'posterno' ) ),
			) );

		Container::make( 'post_meta', esc_html__( 'Administrator notification', 'posterno' ) )
			->set_datastore( $datastore )
			->where( 'post_type', '=', 'pno_emails' )
			->add_fields(
				array(

					Field::make( 'checkbox', 'has_admin_notification', __( 'Notify administrator', 'posterno' ) )
						->set_help_text( esc_html__( 'Enable this option to send an email notification to the administrator.', 'posterno' ) ),

					Field::make( 'text', 'administrator_notification_subject', esc_html__( 'Administrator notification subject', 'posterno' ) )
						->set_conditional_logic(
							array(
								'relation' => 'AND',
								array(
									'field'   => 'has_admin_notification',
									'value'   => true,
									'compare' => '=',
								),
							)
						)
						->set_help_text( esc_html__( 'Enter the subject of the notification sent to the administrators.', 'posterno' ) ),

					Field::make( 'rich_text', 'administrator_notification', esc_html__( 'Administrator notification content', 'posterno' ) )
						->set_conditional_logic(
							array(
								'relation' => 'AND',
								array(
									'field'   => 'has_admin_notification',
									'value'   => true,
									'compare' => '=',
								),
							)
						)
						->set_help_text( esc_html__( 'Enter the content of the notification sent to the administrators.', 'posterno' ) ),
				)
			);

		Container::make( 'post_meta', esc_html__( 'Available email template tags:', 'posterno' ) )
			->where( 'post_type', '=', 'pno_emails' )
			->set_context( 'carbon_fields_after_title' )
			->set_context( 'side' )
			->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'html', 'pno_email_tags_list' )
						->set_html( pno_get_emails_tags_list() ),
				)
			);

		Container::make( 'post_meta', esc_html__( 'Situations', 'posterno' ) )
			->where( 'post_type', '=', 'pno_emails' )
			->set_context( 'side' )
			->set_priority( 'default' )
			->add_fields(
				array(
					Field::make( 'set', 'email_situations', '' )
						->set_datastore( new PNO\Datastores\EmailSituations() )
						->set_help_text( 'Choose when this email will be sent.' )
						->add_options( 'pno_get_emails_situations' ),
				)
			);

	}

}

( new PNO_Emails_Editor_Settings() )->init();
