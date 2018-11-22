<?php
/**
 * List of functions that manage forms powered by Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get a list of form types for populating form type taxonomy terms.
 *
 * @return array {
 *     The array of form types and their schema.
 *     @type string $description The description of the type of form.
 * }
 */
function pno_forms_get_type_schema() {

	$types = array(
		'listing_submission_forms'      => [
			'description' => esc_html__( 'Description goes here' ),
		],
		'profile_forms' => [
			'description' => esc_html__( 'Description goes here' ),
		],
	);

	/**
	 * Allow developers to customize the form types available within the plugin.
	 *
	 * @param array $types the list of defined form types.
	 * @return array
	 */
	return apply_filters( 'pno_form_types_schema', $types );
}
