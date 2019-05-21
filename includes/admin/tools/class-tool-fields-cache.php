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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Fields cache handler tool.
 */
class FieldsCache {

	/**
	 * Holds the form instance.
	 *
	 * @var Form
	 */
	public $form;

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->form = Form::createFromConfig( $this->getFields() );
	}

	/**
	 * Get fields for the forms.
	 *
	 * @return array
	 */
	public function getFields() {

		$fields = [
			'username' => [
				'type'       => 'select',
				'label'      => esc_html__( 'Select which fields:' ),
				'required'   => true,
				'values'     => [
					'registration' => esc_html__( 'Registration fields' ),
					'profile'      => esc_html__( 'Profile fields' ),
					'listings'     => esc_html__( 'Listings fields' ),
					'all'          => esc_html__( 'All fields' ),
				],
				'attributes' => [
					'class' => 'form-control',
				],
			],
			'submit'   => [
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
				<span><?php esc_html_e( 'Custom fields cache' ); ?></span>
			</h2>
			<div class="inside">

				<form action="<?php echo esc_url( $this->form->getAction() ); ?>" method="post" enctype="multipart/form-data">
					<?php foreach ( $this->form->getFields() as $field ) : ?>

						<?php if ( ! empty( $field->getLabel() ) ) : ?>
							<label for="<?php echo esc_attr( $field->getName() ); ?>"><?php echo esc_html( $field->getLabel() ); ?></label>
						<?php endif; ?>

						<?php echo $field->render(); ?>

					<?php endforeach; ?>
				</form>
			</div>
		</div>
		<?php

	}

}

( new FieldsCache() )->init();
