<?php
/**
 * Handles the fields cache tool.
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
 * Fields cache handler tool.
 */
class FieldsCache {

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
	public $form_name = 'fields-cache';

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
				'label'      => esc_html__( 'Select which fields:', 'posterno' ),
				'required'   => true,
				'values'     => [
					'registration' => esc_html__( 'Registration fields', 'posterno' ),
					'profile'      => esc_html__( 'Profile fields', 'posterno' ),
					'listings'     => esc_html__( 'Listings fields', 'posterno' ),
					'all'          => esc_html__( 'All fields', 'posterno' ),
				],
				'attributes' => [
					'class' => 'form-control',
				],
			],
			'submit' => [
				'type'       => 'button',
				'value'      => esc_html__( 'Clear cache', 'posterno' ),
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
		add_action( 'pno_tools_cache', [ $this, 'page' ] );
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
				<span><?php esc_html_e( 'Custom fields cache', 'posterno' ); ?></span>
			</h2>
			<div class="inside">
				<p><?php esc_html_e( 'Use this tool to erase the cache of your fields.', 'posterno' ); ?></p>
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

			if ( $type === 'registration' ) {
				\PNO\Cache\Helper::flush_fields_cache( 'registration' );
			} elseif ( $type === 'profile' ) {
				\PNO\Cache\Helper::flush_fields_cache( 'profile' );
			} elseif ( $type === 'listings' ) {
				\PNO\Cache\Helper::flush_fields_cache( 'listing' );
			} elseif ( $type === 'all' ) {
				\PNO\Cache\Helper::flush_all_fields_cache();
			}

			$url = add_query_arg( [ 'pno-tool-updated' => 'cache' ], admin_url( 'tools.php?page=posterno-tools&tab=cache' ) );

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

		if ( isset( $_GET['pno-tool-updated'] ) && $_GET['pno-tool-updated'] === 'cache' ) {

			$message = esc_html__( 'Fields cache successfully flushed.', 'posterno' );

			posterno()->admin_notices->register_notice( 'cache_flush', 'success', $message, [ 'dismissible' => false ] );

		}

	}

}

( new FieldsCache() )->init();
