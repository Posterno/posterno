<?php
/**
 * Handles display and processing of the login form.
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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class of the login form.
 */
class ListingSubmission {

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
	public $form_name = 'listingSubmission';

	/**
	 * List of steps for the submission.
	 *
	 * @var array
	 */
	public $steps = [];

	/**
	 * Currently active step for the submission.
	 *
	 * @var string
	 */
	public $currentStep = null;

	/**
	 * ID number of the selected listing type, if any.
	 *
	 * @var integer|boolean
	 */
	public $listingType = false;

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var \PNO\Forms\ListingSubmission The single instance of the class
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
		$this->form        = Form::createFromConfig( $this->getFields() );
		$this->steps       = $this->getSteps();
		$this->currentStep = $this->getCurrentlyActiveStep();
		$this->listingType = $this->setListingType();
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
		add_action( 'pno_listing_submission_form_step_listing-type', [ $this, 'selectListingType' ] );
	}

	/**
	 * Retrieve the list of steps defined for the submission form.
	 *
	 * @return array
	 */
	public function getSteps() {

		$steps = [
			'listing-type'    => [
				'name'     => esc_html__( 'Select a listing type', 'posterno' ),
				'priority' => 1,
			],
			'listing-details' => [
				'name'     => esc_html__( 'Listing details', 'posterno' ),
				'priority' => 2,
			],
		];

		/**
		 * Filter: allows customization of the steps for the listing submission form.
		 *
		 * @param array $steps the list of steps.
		 * @return array
		 */
		$steps = apply_filters( 'pno_listing_submission_form_steps', $steps );

		uasort( $steps, 'pno_sort_array_by_priority' );

		return $steps;

	}

	/**
	 * Retrieve the currently active step.
	 *
	 * @return string
	 */
	public function getCurrentlyActiveStep() {

		$step  = false;
		$steps = $this->getSteps();

		if ( is_page( pno_get_listing_submission_page_id() ) && isset( $_GET['submission_step'] ) && ! empty( $_GET['submission_step'] ) ) {
			$step = sanitize_text_field( $_GET['submission_step'] );
		} else {
			$step = key( $steps );
		}

		return $step;

	}

	/**
	 * Get the next available step.
	 *
	 * @return string|boolean
	 */
	public function getNextStep() {
		return pno_get_adjacent_array_key( $this->getCurrentlyActiveStep(), $this->getSteps(), +1 );
	}

	/**
	 * Set a listing type id for the current submisison.
	 *
	 * @return void
	 */
	public function setListingType() {
		$this->listingType = isset( $_GET['listing_type_id'] ) && ! empty( $_GET['listing_type_id'] ) ? absint( $_GET['listing_type_id'] ) : false;
	}

	/**
	 * Get the currently set listing type id number.
	 *
	 * @return string|int|boolean
	 */
	public function getListingType() {
		return absint( $this->listingType );
	}

	/**
	 * Get fields for the form.
	 *
	 * @return array
	 */
	protected function getFields() {

		$necessaryFields = [
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
				'priority'   => 99,
			],
			'submit'      => [
				'type'       => 'button',
				'value'      => esc_html__( 'Submit listing', 'posterno' ),
				'attributes' => [
					'class' => 'btn btn-primary',
				],
				'priority'   => 100,
			],
		];

		$fields = array_merge( pno_get_listing_submission_fields(), $necessaryFields );

		/**
		 * Filter: allows customization of the fields for the listing submission form.
		 *
		 * @param array $fields the list of fields.
		 * @return array
		 */
		$fields = apply_filters( 'pno_listing_submission_form_fields', $fields );

		uasort( $fields, 'pno_sort_array_by_priority' );

		return $fields;

	}

	/**
	 * Displays the content for the listing type selection step.
	 *
	 * @return void
	 */
	public function selectListingType() {

		posterno()->templates
			->set_template_data(
				[
					'action' => $this->form->getAction(),
					'step'   => $this->getNextStep(),
					'title'  => $this->steps[ $this->getCurrentlyActiveStep() ]['name'],
				]
			)
			->get_template_part( 'forms/listing-type-selection' );

	}

	/**
	 * Render the form.
	 *
	 * @return void
	 */
	public function render() {

		if ( $this->getCurrentlyActiveStep() === 'listing-details' ) {

			$this->form->prepareForView();

			posterno()->templates
				->set_template_data(
					[
						'form'      => $this->form,
						'form_name' => $this->form_name,
						'title'     => esc_html__( 'Listing details', 'posterno' ),
					]
				)
				->get_template_part( 'new-form' );

		} else {

			$step = $this->getCurrentlyActiveStep();

			/**
			 * Hook: allow developers to display the content of custom listing submission steps.
			 */
			do_action( "pno_listing_submission_form_step_{$step}" );

		}
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

				print_r( $this->form->toArray() );
				exit;

			}
		} catch ( Exception $e ) {
			$this->form->setProcessingError( $e->getMessage(), $e->getErrorCode() );
			return;
		}
	}

}
