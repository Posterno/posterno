<?php
/**
 * Main class responsible of handling Posterno's forms layout.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Layout;

use PNO\Form\Field\AbstractField;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Responsible of rendering all PNO's forms on the frontend.
 */
class DefaultLayout extends AbstractLayout {

	/**
	 * Render fields within a form.
	 *
	 * @param AbstractField $field the field to display.
	 * @return string
	 */
	public function render_field( AbstractField $field ) {

		ob_start();

		/**
		 * Action that triggers before displaying a field within a form.
		 *
		 * @param AbstractField $field the field being processed.
		 */
		do_action( 'pno_form_before_field', $field );

		?>

		<div <?php pno_form_field_class( $field ); ?>>

			<?php if ( $field instanceof \PNO\Form\Field\CheckboxField ) : ?>

				<?php $field->render(); ?>

			<?php else : ?>

				<label for="<?php echo esc_attr( $field->get_id() ); ?>">
					<?php echo esc_html( $field->get_label() ); ?>
					<?php if ( ! $field->get_option( 'required' ) ) : ?>
						<small class="pno-optional"><?php esc_html_e( '(optional)' ); ?></small>
					<?php endif; ?>
				</label>

				<?php $field->render(); ?>

			<?php endif; ?>

			<?php if ( $field->has_errors() ) : ?>
				<div class="invalid-feedback">
					<?php foreach ( $field->get_errors() as $error ) : ?>
						<p><?php echo esc_html( $error ); ?></p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $field->get_option( 'description' ) ) ) : ?>
				<small class="form-text text-muted">
					<?php echo esc_html( $field->get_option( 'description' ) ); ?>
				</small>
			<?php endif; ?>

		</div>

		<?php

		/**
		 * Action that triggers after displaying a field within a form.
		 *
		 * @param AbstractField $field the field being processed.
		 */
		do_action( 'pno_form_after_field', $field );

		return ob_get_clean();

		/*
		$html = '<div>';
		if ( $field instanceof \PNO\Form\Field\CheckboxField ) {
			if ( $field->has_label() ) {
				$html .= "<label>{$field->render()}&nbsp;{$field->get_label()}</label>";
			} else {
				$html .= $field->render();
			}
		} elseif ( $field instanceof \PNO\Form\Field\AbstractGroup ) {
			if ( $field->has_label() ) {
				$html .= "<label>{$field->get_label()}</label>";
			}
			foreach ( $field->get_choices() as $choice => $label ) {
				$html .= "<label>{$field->render_choice($choice)}&nbsp;{$label}</label>";
			}
		} else {
			if ( $field->has_label() ) {
				$html .= "<label>{$field->get_label()}</label>";
			}
			$html .= $field->render();
		}
		if ( $field->has_errors() ) {
			$html .= '<ul class="errors">';
			foreach ( $field->get_errors() as $error ) {
				$html .= "<li>{$error}</li>";
			}
			$html .= '</ul>';
		}
		$html .= '</div>';

		return $html;*/
	}

}
