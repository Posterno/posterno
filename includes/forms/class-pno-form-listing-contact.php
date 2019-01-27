<?php
/**
 * Handles display and processing of the listing contact author form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

use PNO\Exception;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles the form.
 */
class PNO_Form_Listing_Contact extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'listing-contact';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Login The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Returns static instance of class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'process' ) );

		$this->steps = (array) apply_filters(
			'pno_listing_contact_form_steps',
			array(
				'submit' => array(
					'name'     => __( 'Submit Details' ),
					'view'     => array( $this, 'submit' ),
					'handler'  => array( $this, 'submit_handler' ),
					'priority' => 10,
				),
			)
		);

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( intval( $_POST['step'] ), array_keys( $this->steps ), true );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( intval( $_GET['step'] ), array_keys( $this->steps ), true );
		}

	}

	/**
	 * Initializes the fields used in the form.
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$fields = array(
			'contact' => array(
				'name'    => array(
					'label'       => esc_html__( 'Your name' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1,
				),
				'email'   => array(
					'label'       => esc_html__( 'Your email address' ),
					'type'        => 'email',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 2,
				),
				'message' => array(
					'label'       => esc_html__( 'Message' ),
					'type'        => 'textarea',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 3,
				),
			),
		);

		/**
		 * Allow developers to customize the listing contact form fields.
		 *
		 * @param array $fields list of fields defined for the form.
		 * @return array
		 */
		$this->fields = apply_filters( 'pno_listing_contact_form_fields', $fields );

	}

	/**
	 * Displays the form.
	 */
	public function submit() {

		$this->init_fields();

		posterno()->templates
			->set_template_data(
				[
					'form'         => $this,
					'action'       => $this->get_action(),
					'fields'       => $this->get_fields( 'contact' ),
					'step'         => $this->get_step(),
					'submit_label' => esc_html__( 'Send email' ),
				]
			)
			->get_template_part( 'form' );

	}

	/**
	 * Handles the submission of form data.
	 *
	 * @throws Exception On validation error.
	 */
	public function submit_handler() {
		try {

			if ( empty( $_POST[ 'submit_' . $this->form_name ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ "{$this->form_name}_nonce" ], "verify_{$this->form_name}_form" ) ) {
				return;
			}

			//phpcs:ignore
			if ( ! isset( $_POST[ 'pno_form' ] ) || isset( $_POST['pno_form'] ) && $_POST['pno_form'] !== $this->form_name ) {
				return;
			}

			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			$listing              = get_queried_object();
			$listing_id           = isset( $listing->ID ) ? absint( $listing->ID ) : false;
			$listing_author_id    = isset( $listing->post_author ) ? absint( $listing->post_author ) : false;
			$listing_author_email = pno_get_user_email( $listing_author_id );

			$sender_name    = isset( $values['contact']['name'] ) ? sanitize_text_field( $values['contact']['name'] ) : false;
			$sender_email   = isset( $values['contact']['email'] ) ? sanitize_email( $values['contact']['email'] ) : false;
			$sender_message = isset( $values['contact']['message'] ) ? esc_html( $values['contact']['message'] ) : false;

			if ( $sender_email && $sender_name && $sender_message ) {

				pno_send_email(
					'core_listing_author_email',
					$listing_author_email,
					[
						'user_id'    => $listing_author_id,
						'listing_id' => $listing_id,
					]
				);

				$message = esc_html__( 'Your message has been sent successfully.' );

				/**
				 * Allow developers to customize the message displayed after successfully sending a message to the listing's author.
				 *
				 * @param string $message the message.
				 */
				$message = apply_filters( 'pno_listing_contact_form_success_message', $message );

				$this->unbind();
				$this->set_as_successful();
				$this->set_success_message( $message );
				return;

			} else {
				throw new Exception( esc_html__( 'Something went wrong while sending the message.' ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
