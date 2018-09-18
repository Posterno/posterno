<?php
/**
 * Custom category walker for listings fields.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Walks through categories.
 */
class PNO_Category_Walker extends Walker {

	/**
	 * Tree type that the class handles.
	 *
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @var array
	 */
	public $db_fields = array(
		'parent' => 'parent',
		'id'     => 'term_id',
		'slug'   => 'slug',
	);

	/**
	 * Start the list walker.
	 *
	 * @see Walker::start_el()
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $object Category data object.
	 * @param int    $depth Depth of category in reference to parents.
	 * @param array  $args args.
	 * @param int    $current_object_id object id.
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {

		if ( ! empty( $args['hierarchical'] ) ) {
			$pad = str_repeat( '&nbsp;', $depth * 3 );
		} else {
			$pad = '';
		}

		$cat_name = apply_filters( 'list_product_cats', $object->name, $object );

		$value = isset( $args['value'] ) && 'id' === $args['value'] ? $object->term_id : $object->slug;

		$output .= "\t<option class=\"level-" . intval( $depth ) . '" value="' . esc_attr( $value ) . '"';

		if ( isset( $args['selected'] ) && (
				$value == $args['selected'] // phpcs:ignore WordPress.PHP.StrictComparisons
				|| ( is_array( $args['selected'] ) && in_array( $value, $args['selected'] ) ) // phpcs:ignore WordPress.PHP.StrictInArray
			)
		) {
			$output .= ' selected="selected"';
		}

		$output .= '>';

		$output .= $pad . esc_html( $cat_name );

		if ( ! empty( $args['show_count'] ) ) {
			$output .= '&nbsp;(' . intval( $object->count ) . ')';
		}

		$output .= "</option>\n";
	}
}
