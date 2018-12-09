<?php
/**
 * Handles display and processing of the listing editing form.
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
class PNO_Form_Listing_Edit extends PNO_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	public $form_name = 'listing-edit';

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var PNO_Form_Listing_Editi The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Holds the ID of the listing we're going to modify.
	 *
	 * @var integer
	 */
	public $listing_id = 0;

	/**
	 * Holds the ID of the user that wants to edit the listing.
	 *
	 * @var integer
	 */
	public $user_id = 0;

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

		$this->listing_id = pno_get_queried_listing_editable_id();

		$this->user_id = $this->get_user_id();

		add_action( 'wp', array( $this, 'process' ) );

		$steps = array(
			'listing-details' => array(
				'name'     => esc_html__( 'Listing details' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 20,
			),
		);

		/**
		 * List of steps for the listing editing form.
		 *
		 * @since 0.1.0
		 * @param array $steps the list of steps for the listing editing form.
		 */
		$this->steps = (array) apply_filters( 'pno_listing_editing_form_steps', $steps );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( intval( $_POST['step'] ), array_keys( $this->steps ), true );
		} elseif ( ! empty( $_GET['step'] ) ) {
			$this->step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( intval( $_GET['step'] ), array_keys( $this->steps ), true );
		}

	}

	/**
	 * Retrieve the ID of the listing we're going to modify.
	 *
	 * @return mixed
	 */
	private function get_listing_id() {
		return isset( $_GET['listing_id'] ) ? absint( $_GET['listing_id'] ) : false;
	}

	/**
	 * Retrieve the ID of the user currently logged in.
	 *
	 * @return mixed
	 */
	private function get_user_id() {
		return is_user_logged_in() ? get_current_user_id() : false;
	}

	/**
	 * Initializes the fields used in the form.
	 */
	public function init_fields() {

		if (
			! is_user_logged_in() ||
			! is_page( pno_get_listing_editing_page_id() ) ||
			! $this->listing_id ||
			! pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) ||
			( pno_is_listing_pending_approval( $this->listing_id ) && pno_is_user_owner_of_listing( $this->user_id, $this->listing_id ) && ! pno_pending_listings_can_be_edited() ) ) {
			return;
		}

		if ( $this->fields ) {
			return;
		}

		$fields = array(
			'listing-details' => pno_get_listing_submission_fields( $this->listing_id ),
		);

		$this->fields = $fields;

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
			'form_type'    => 'listing',

		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'form' );

	}

}
