<?php
/**
 * Handles display and processing of the listing submission form.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class that handles the form.
 */
class PNO_Form_Listing_Submission extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'listing-submission';

	/**
	 * Holds the selected listing type id.
	 *
	 * @var string
	 */
	public $listing_type_id = null;

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Listing_Submission The single instance of the class
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
			'listing-type' => array(
				'name'     => esc_html__( 'Select a listing type' ),
				'view'     => array( $this, 'type' ),
				'handler'  => array( $this, 'type_handler' ),
				'priority' => 10,
			),
			'listing-details' => array(
				'name'     => esc_html__( 'Listing details' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 20,
			),
		);

		/**
		 * List of steps for the listing submission form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the listing submission form.
		 */
		$this->steps = (array) apply_filters( 'pno_listing_submission_form_steps', $steps );

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
			'listing-details' => array(
				'listing_type' => array(
					'label'       => esc_html__( 'Select the listing type' ),
					'description' => false,
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 0,
				),
			),
		);

		$this->fields = $fields;

	}

	/**
	 * Display the listing type selection boxes.
	 *
	 * @return void
	 */
	public function type() {

		$this->init_fields();

		posterno()->templates
			->set_template_data(
				[
					'form'         => $this,
					'action'       => $this->get_action(),
					'fields'       => false,
					'step'         => $this->get_step(),
					'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
					'submit_label' => esc_html__( 'Continue' ),
				]
			)
			->get_template_part( 'forms/listing-type-selection' );

	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function type_handler() {
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

			$type_id = isset( $_POST['listing_type_id'] ) ? absint( $_POST['listing_type_id'] ) : false;

			if ( $type_id ) {
				$this->listing_type_id = $type_id;
				$this->step ++;
			} else {
				throw new Exception( esc_html__( 'Something went wrong.' ) );
			}

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Displays the listing details form.
	 *
	 * @return void
	 */
	public function submit() {

		$this->init_fields();

		$data = [
			'form'         => $this,
			'action'       => $this->get_action(),
			'fields'       => $this->get_fields( 'listing-details' ),
			'step'         => $this->get_step(),
			'title'        => $this->steps[ $this->get_step_key( $this->get_step() ) ]['name'],
			'submit_label' => esc_html__( 'Save changes' ),
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'form' );

	}

}
