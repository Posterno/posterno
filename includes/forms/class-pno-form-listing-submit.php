<?php
/**
 * Handles the listing submission form processing.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class that handles the submission form.
 */
class PNO_Form_Listing_Submit extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'listing-submit';

	/**
	 * Listing ID.
	 *
	 * @access protected
	 * @var int
	 */
	protected $listing_id = 0;

	/**
	 * Store the selected listing type.
	 *
	 * @var string
	 */
	protected $listing_type = '';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Listing_Submit The single instance of the class
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

		$steps = array(
			'select_type'  => array(
				'name'     => esc_html__( 'Select listing type' ),
				'view'     => array( $this, 'type_selection' ),
				'handler'  => array( $this, 'type_selection_handler' ),
				'priority' => 1,
			),
			'submit'  => array(
				'name'     => esc_html__( 'Listing details' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10,
			),
			'preview'  => array(
				'name'     => esc_html__( 'Preview listing' ),
				'view'     => array( $this, 'preview' ),
				'handler'  => array( $this, 'preview_handler' ),
				'priority' => 20,
			),
			'done' => array(
				'name'     => esc_html__( 'Listing succesfully submitted' ),
				'view'     => array( $this, 'done' ),
				'handler'  => false,
				'priority' => 30,
			),
		);

		/**
		 * List of steps for the listing submission form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the form.
		 */
		$this->steps = (array) apply_filters( 'pno_listing_submission_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) { //phpcs:ignore
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( intval( $_POST['step'] ), array_keys( $this->steps ), true ); //phpcs:ignore
		} elseif ( ! empty( $_GET['step'] ) ) { //phpcs:ignore
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( intval( $_GET['step'] ), array_keys( $this->steps ), true ); //phpcs:ignore
		}

	}

	/**
	 * Defines the fields of the submission form.
	 *
	 * @return void
	 */
	public function init_fields() {

		if ( $this->fields ) {
			return;
		}

		$this->fields = pno_get_listing_submission_fields();

	}

	/**
	 * Display the listings type selection form.
	 *
	 * @return void
	 */
	public function type_selection() {

		posterno()->templates
			->set_template_data(
				[
					'form'         => $this->form_name,
					'action'       => $this->get_action(),
					'fields'       => $this->get_fields( $this->get_step_key() ),
					'step'         => $this->get_step(),
					'steps'        => $this->get_steps(),
					'active_step'  => $this->get_step_key(),
					'submit_label' => $this->get_submit_button_label(),
					'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
				]
			)
			->get_template_part( 'forms/listing', 'type-selection' );

	}

	/**
	 * Handles verification of the submitted details.
	 *
	 * @return void
	 */
	public function type_selection_handler() {
		try {

			if ( empty( $_POST['submit_listing-submit'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['listing-submit_nonce'], 'verify_listing-submit_form' ) ) {
				return;
			}

			// Successful, show next step.
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Handles the display of the login form.
	 *
	 * @return void
	 */
	public function submit() {

		$this->init_fields();

		$data = [
			'form'         => $this->form_name,
			'action'       => $this->get_action(),
			'fields'       => $this->get_fields( $this->get_step_key() ),
			'step'         => $this->get_step(),
			'steps'        => $this->get_steps(),
			'active_step'  => $this->get_step_key(),
			'submit_label' => $this->get_submit_button_label(),
			'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'listing-submission' );

	}

	/**
	 * Handles verification of the submitted details.
	 *
	 * @return void
	 */
	public function submit_handler() {
		try {
			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			if ( empty( $_POST['submit_listing-submit'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['listing-submit_nonce'], 'verify_listing-submit_form' ) ) {
				return;
			}

			// Validate required.
			$validation_status = $this->validate_fields( $values );

			if ( is_wp_error( $validation_status ) ) {
				throw new Exception( $validation_status->get_error_message() );
			}

			// Successful, show next step.
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Detect the label we're going to use within the form.
	 *
	 * How it works:
	 * - If the next step isn't preview/done - we show the "Next" label.
	 * - If the next step is "preview" - we show the "Preview listing" label.
	 * - If the next step is "done" - we show the "Submit listing" label.
	 *
	 * @return string
	 */
	private function get_submit_button_label() {

		$label = __( 'Continue &rarr;' );

		$keys      = array_keys( $this->steps );
		$next_step = $keys[ array_search( $this->get_step_key(), $keys, true ) + 1 ];

		if ( $next_step === 'preview' ) {
			$label = esc_html__( 'Preview listing' );
		} elseif ( $next_step === 'done' ) {
			$label = esc_html__( 'Complete submission' );
		}

		/**
		 * Allow developers to modify the listings form submission button label.
		 *
		 * @param string $label     current label string.
		 * @param string $next_step the next step key we're using to adjust the label.
		 * @return string
		 */
		return apply_filters( 'pno_listings_submission_form_btn_label', $label, $next_step );

	}

}
