<?php
/**
 * Handles the settings export tool.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Admin\Tools;

use PNO\Form\Form;
use PNO\Form\DefaultSanitizer;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Fields cache handler tool.
 */
class SettingsExport {

	use DefaultSanitizer;

	/**
	 * Holds the form instance.
	 *
	 * @var Form
	 */
	public $form;

	/**
	 * Holds the name of this form.
	 *
	 * @var string
	 */
	public $form_name = 'settings-export';

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form = Form::createFromConfig( $this->getFields() );
		$this->addSanitizer( $this->form );
	}

	/**
	 * Get fields for the forms.
	 *
	 * @return array
	 */
	public function getFields() {

		$fields = [
			'submit' => [
				'type'       => 'button',
				'value'      => esc_html__( 'Export settings', 'posterno' ),
				'attributes' => [
					'class' => 'button-primary',
				],
			],
		];

		return $fields;

	}

	/**
	 * Hook into WP.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'pno_tools_export', [ $this, 'page' ] );
		add_action( 'admin_init', [ $this, 'process' ] );
	}

	/**
	 * Displays content of the page.
	 *
	 * @return void
	 */
	public function page() {

		?>
		<div class="postbox" id="export-settings">
			<h2 class="hndle ui-sortable-handle">
				<span><?php esc_html_e( 'Export settings', 'posterno' ); ?></span>
			</h2>
			<div class="inside">
			<p><?php esc_html_e( 'Export the Posterno settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'posterno' ); ?></p>
				<form action="<?php echo esc_url( $this->form->getAction() ); ?>" method="post" enctype="multipart/form-data">
					<input type="hidden" name="pno_form" value="<?php echo esc_attr( $this->form_name ); ?>" />
					<?php wp_nonce_field( 'verify_' . esc_attr( $this->form_name ) . '_form', esc_attr( $this->form_name ) . '_nonce' ); ?>
					<?php foreach ( $this->form->getFields() as $field ) : ?>
						<?php echo $field->render(); ?>
					<?php endforeach; ?>
				</form>
			</div>
		</div>
		<?php

	}

	/**
	 * Process settings export.
	 *
	 * @return void
	 */
	public function process() {

		//phpcs:ignore
		if ( ! isset( $_POST[ 'pno_form' ] ) || isset( $_POST['pno_form'] ) && $_POST['pno_form'] !== $this->form_name ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ "{$this->form_name}_nonce" ], "verify_{$this->form_name}_form" ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'pno_settings' );

		ignore_user_abort( true );

		if ( ! pno_is_func_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'pno_settings_export_filename', 'posterno-settings-export-' . date( 'm-d-Y' ) ) . '.json' );
		header( 'Expires: 0' );
		echo wp_json_encode( $settings );
		exit;

	}

}

( new SettingsExport() )->init();
