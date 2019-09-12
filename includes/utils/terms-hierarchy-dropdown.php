<?php
/**
 * Display taxonomy terms hierarchy separated by optgroup tag.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class that handles the hierarchy optgroup tag in dropdowns.
 */
class TermsHierarchyDropdown extends \Walker_CategoryDropdown {

	/**
	 * Starting opening element of the walker.
	 *
	 * @see Walker::start_el()
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $object Category data object.
	 * @param int    $depth Depth of category in reference to parents.
	 * @param array  $args args.
	 * @param int    $current_object_id object id.
	 * @return string
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

		$pad = '&nbsp;';

		$cat_name = apply_filters( 'list_cats', $category->name, $category );

		if ( 0 == $depth ) {
			$output .= "<optgroup class=\"level-$depth\" label=\"" . $cat_name . '" >';
		} else {
			$output .= "<option class=\"level-$depth\" value=\"" . $category->term_id . '"';
			if ( $category->term_id == $args['selected'] ) {
				$output .= ' selected="selected"';
			}
			$output .= '>';
			$output .= $pad . $cat_name;
			if ( $args['show_count'] ) {
				$output .= '&nbsp;&nbsp;(' . $category->count . ')';
			}
			$output .= '</option>';
		}

	}

	/**
	 * Undocumented function
	 *
	 * @see Walker::start_el()
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $object Category data object.
	 * @param int    $depth Depth of category in reference to parents.
	 * @param array  $args args.
	 * @return string
	 */
	function end_el( &$output, $object, $depth = 0, $args = [] ) {
		if ( 0 == $depth ) {
			$output .= '</optgroup>';
		}
	}

}
