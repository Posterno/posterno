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
	protected $listing_id;

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

		$steps = pno_get_listings_submission_form_steps();

		if ( is_array( $steps ) && ! empty( $steps ) ) {
			foreach ( $steps as $stepkey => $step_details ) {

				$is_taxonomy_step = isset( $step_details['taxonomy'] ) && ! empty( $step_details['taxonomy'] ) ? $step_details['taxonomy'] : false;

				if ( $is_taxonomy_step ) {
					$this->steps[ sanitize_key( $stepkey ) ] = [
						'name'     => esc_html( $step_details['title'] ),
						'view'     => array( $this, 'submit' ),
						'handler'  => array( $this, 'taxonomy_handler' ),
						'priority' => absint( $step_details['priority'] ),
						'taxonomy' => $is_taxonomy_step,
					];
				} else {

					if ( $stepkey === 'submit' ) {
						$this->steps[ sanitize_key( $stepkey ) ] = [
							'name'     => esc_html( $step_details['title'] ),
							'view'     => array( $this, 'submit' ),
							'handler'  => array( $this, 'submit_handler' ),
							'priority' => absint( $step_details['priority'] ),
						];
					} elseif ( $stepkey === 'preview' ) {
						$this->steps[ sanitize_key( $stepkey ) ] = [
							'name'     => esc_html( $step_details['title'] ),
							'view'     => array( $this, 'preview' ),
							'handler'  => array( $this, 'preview_handler' ),
							'priority' => absint( $step_details['priority'] ),
						];
					} elseif ( $stepkey === 'done' ) {
						$this->steps[ sanitize_key( $stepkey ) ] = [
							'name'     => esc_html( $step_details['title'] ),
							'view'     => array( $this, 'done' ),
							'priority' => absint( $step_details['priority'] ),
						];
					}
				}
			}
		}

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

		$label = __( 'Next &raquo;' );

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
