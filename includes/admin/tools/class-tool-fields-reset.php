<?php
/**
 * Handles the fields reset tool.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Admin\Tools;

use PNO\Form\Form;
use PNO\Form\DefaultSanitizer;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Fields reset handler tool.
 */
class FieldsReset {

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
	public $form_name = 'fields-reset';

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
			'type'   => [
				'type'       => 'select',
				'label'      => esc_html__( 'Reset:', 'posterno' ),
				'required'   => true,
				'values'     => [
					'registration' => esc_html__( 'Registration fields', 'posterno' ),
					'profile'      => esc_html__( 'Profile fields', 'posterno' ),
					'listings'     => esc_html__( 'Listings fields', 'posterno' ),
				],
				'attributes' => [
					'class' => 'form-control',
				],
			],
			'submit' => [
				'type'       => 'button',
				'value'      => esc_html__( 'Reset fields', 'posterno' ),
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
		add_action( 'pno_tools_database', [ $this, 'page' ] );
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
		<div class="postbox">
			<h2 class="hndle ui-sortable-handle">
				<span><?php esc_html_e( 'Reset custom fields', 'posterno' ); ?></span>
			</h2>
			<div class="inside">
				<p><?php esc_html_e( 'This tool deletes your custom fields and re-installs default fields.', 'posterno' ); ?></p>
				<form action="<?php echo esc_url( $this->form->getAction() ); ?>" method="post" enctype="multipart/form-data">
					<input type="hidden" name="pno_form" value="<?php echo esc_attr( $this->form_name ); ?>" />
					<?php wp_nonce_field( 'verify_' . esc_attr( $this->form_name ) . '_form', esc_attr( $this->form_name ) . '_nonce' ); ?>
					<table class="form-table">
						<tbody>
							<?php foreach ( $this->form->getFields() as $field ) : ?>
							<tr>
								<th scope="row">
									<?php if ( ! empty( $field->getLabel() ) ) : ?>
										<label for="<?php echo esc_attr( $field->getName() ); ?>"><?php echo esc_html( $field->getLabel() ); ?></label>
									<?php endif; ?>
								</th>
								<td>
									<?php echo $field->render(); ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</form>
			</div>
		</div>
		<?php

	}

	/**
	 * Process cache invalidation.
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

		$this->form->setFieldValues( $_POST );

		if ( $this->form->isValid() ) {

			$type = $this->form->getFieldValue( 'type' );

			// This toggles an admin notice.
			update_option( 'pno_background_custom_fields_generation', true );

			posterno()->queue->schedule_single(
				time(),
				'pno_reset_custom_fields_batch',
				[
					'type'   => $type,
					'offset' => 0,
					'limit'  => 30,
				],
				'pno_reset_custom_fields_batch'
			);

			$url = add_query_arg( [ 'pno-tool-updated' => 'fields-reset' ], admin_url( 'tools.php?page=posterno-tools&tab=database' ) );
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

		if ( isset( $_GET['pno-tool-updated'] ) && $_GET['pno-tool-updated'] === 'fields-reset' ) {

			$message = esc_html__( 'Fields are currently being reset in the background.', 'posterno' );

			posterno()->admin_notices->register_notice( 'settings_imported', 'success', $message, [ 'dismissible' => false ] );

		}

	}

}

( new FieldsReset() )->init();
