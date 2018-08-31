<?php
/**
 * Adjust terms metaboxes in the admin panel to be a radio selector only.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$listings_types_object = new Taxonomy_Single_Term( 'listings-types', array( 'listings' ), 'select' );
$listings_types_object->set( 'allow_new_terms', true );
$listings_types_object->set( 'force_selection', true );
$listings_types_object->set( 'priority', 'default' );
$listings_types_object->set( 'metabox_title', esc_html__( 'Listing type' ) );
