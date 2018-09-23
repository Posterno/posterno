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

		return $html;
	}

}
