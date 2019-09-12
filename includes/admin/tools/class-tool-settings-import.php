<?php
/**
 * Handles the settings import tool.
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
 * Settings import tool
 */
class SettingsImport {

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
	public $form_name = 'settings-import';

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
				'value'      => esc_html__( 'Import settings', 'posterno' ),
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
		add_action( 'pno_tools_import', [ $this, 'page' ] );
		add_action( 'admin_init', [ $this, 'process' ] );
		add_action( 'admin_head', [ $this, 'notice' ] );
	}

	/**
	 * Displays content of the page.
	 *
	 * @return void
	 */
	public function page() {

		?>
		<div class="postbox" id="import-settings">
			<h2 class="hndle ui-sortable-handle">
				<span><?php esc_html_e( 'Import settings', 'posterno' ); ?></span>
			</h2>
			<div class="inside">

				<p><?php esc_html_e( 'Import the Posterno settings from a .json file. This file can be obtained by exporting the settings on another site using the form on the "export" tab.', 'posterno' ); ?></p>

				<form action="<?php echo esc_url( $this->form->getAction() ); ?>" method="post" enctype="multipart/form-data">
					<input type="hidden" name="pno_form" value="<?php echo esc_attr( $this->form_name ); ?>" />
					<?php wp_nonce_field( 'verify_' . esc_attr( $this->form_name ) . '_form', esc_attr( $this->form_name ) . '_nonce' ); ?>
					<p>
						<input type="file" name="import_file"/>
					</p>
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

		if ( pno_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
			wp_die( esc_html__( 'Please upload a valid .json file', 'posterno' ), esc_html__( 'Error', 'posterno' ), array( 'response' => 400 ) );
		}

		$import_file = $_FILES['import_file']['tmp_name'];
		if ( empty( $import_file ) ) {
			wp_die( esc_html__( 'Please upload a file to import', 'posterno' ), esc_html__( 'Error', 'posterno' ), array( 'response' => 400 ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings = pno_object_to_array( json_decode( file_get_contents( $import_file ) ) );

		if ( is_array( $settings ) && ! empty( $settings ) ) {

			update_option( 'pno_settings', $settings );

			$url = add_query_arg( [ 'pno-tool-updated' => 'settings-import' ], admin_url( 'tools.php?page=posterno-tools&tab=import' ) );
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Show success notice.
	 *
	 * @return void
	 */
	public function notice() {

		if ( isset( $_GET['pno-tool-updated'] ) && $_GET['pno-tool-updated'] === 'settings-import' ) {

			$message = esc_html__( 'Settings successfully imported.', 'posterno' );

			posterno()->admin_notices->register_notice( 'settings_imported', 'success', $message, [ 'dismissible' => false ] );

		}

	}

}

( new SettingsImport() )->init();
