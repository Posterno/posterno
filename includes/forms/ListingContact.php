<?php
/**
 * Handles display and processing of the listing contact form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018 - 2019, Sematico, LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Forms;

use PNO\Form\Form;
use PNO\Validator;
use PNO\Exception;
use PNO\Form\DefaultSanitizer;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class of the login form.
 */
class ListingContact {

	use DefaultSanitizer;

	/**
	 * The form object containing all the details about the form.
	 *
	 * @var Form
	 */
	public $form;

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'listingContact';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO\Forms\ListingContact The single instance of the class
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
	 * Get things started.
	 */
	public function __construct() {
		$this->form = Form::createFromConfig( $this->getFields() );
		$this->addSanitizer( $this->form );
		$this->init();
	}

	/**
	 * Get things started.
	 *
	 * @return void
	 */
	public function init() {
		$this->hook();
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_loaded', [ $this, 'process' ] );
	}

	/**
	 * Get fields for the form.
	 *
	 * @return array
	 */
	protected function getFields() {

		$fields = [
			'name'        => [
				'type'       => 'text',
				'label'      => esc_html__( 'Your name', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 1,
			],
			'email'       => [
				'type'       => 'email',
				'label'      => esc_html__( 'Your email address', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 2,
			],
			'message'     => [
				'type'       => 'textarea',
				'label'      => esc_html__( 'Message', 'posterno' ),
				'required'   => true,
				'attributes' => [
					'class' => 'form-control',
					'rows'  => 4,
				],
				'priority'   => 3,
			],
			/**
			 * Honeypot field.
			 */
			'hp-comments' => [
				'type'       => 'text',
				'label'      => esc_html__( 'If you\'re human leave this blank:', 'posterno' ),
				'validators' => new Validator\BeEmpty(),
				'attributes' => [
					'class' => 'form-control',
				],
				'priority'   => 4,
			],
			'submit-form' => [
				'type'       => 'button',
				'value'      => esc_html__( 'Send email', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 100,
			],
		];

		/**
		 * Filter: allows customization of the fields for the listing contact form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_listing_contact_form_fields', $fields );

		uasort( $fields, 'pno_sort_array_by_priority' );

		return $fields;

	}

	/**
	 * Render the form.
	 *
	 * @return void
	 */
	public function render() {

		$this->form->filterValues();
		$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
					]
				)
				->get_template_part( 'form' );

	}

	/**
	 * Process the form.
	 *
	 * @throws Exception When there's an error during credentials process.
	 * @return void
	 */
	public function process() {
		try {

			//phpcs:ignore
			if ( ! isset( $_POST[ 'pno_form' ] ) || isset( $_POST['pno_form'] ) && $_POST['pno_form'] !== $this->form_name ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ "{$this->form_name}_nonce" ], "verify_{$this->form_name}_form" ) ) {
				return;
			}

			$this->form->setFieldValues( $_POST );

			if ( $this->form->isValid() ) {

				$listing              = get_queried_object();
				$listing_id           = isset( $listing->ID ) ? absint( $listing->ID ) : false;
				$listing_author_id    = isset( $listing->post_author ) ? absint( $listing->post_author ) : false;
				$listing_author_email = pno_get_user_email( $listing_author_id );

				$sender_name    = $this->form->getFieldValue( 'name' );
				$sender_email   = $this->form->getFieldValue( 'email' );
				$sender_message = $this->form->getFieldValue( 'message' );

				if ( $sender_email && $sender_name && $sender_message ) {

					pno_send_email(
						'core_listing_author_email',
						$listing_author_email,
						[
							'user_id'        => $listing_author_id,
							'listing_id'     => $listing_id,
							'sender_name'    => $sender_name,
							'sender_email'   => $sender_email,
							'sender_message' => $sender_message,
						]
					);

					$message = esc_html__( 'Your message has been sent successfully.', 'posterno' );

					/**
					 * Allow developers to customize the message displayed after successfully sending a message to the listing's author.
					 *
					 * @param string $message the message.
					 */
					$message = apply_filters( 'pno_listing_contact_form_success_message', $message );

					$this->form->setSuccessMessage( $message );
					$this->form->reset();
					return;
				} else {
					throw new Exception( esc_html__( 'Something went wrong while sending the message.', 'posterno' ) );
				}
			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
