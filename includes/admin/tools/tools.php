<?php
/**
 * Admin tools.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Displays content of the tools page.
 *
 * @return void
 */
function pno_tools_page() {

	$active_tab = isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'cache';

	?>

	<div class="pno-admin-title-area">
		<div class="wrap">
			<h2><?php esc_html_e( 'Posterno tools' ); ?></h2>
			<ul class="title-links hidden-sm-and-down">
				<li>
					<a href="https://docs.posterno.com/" target="_blank" class="page-title-action">Documentation</a>
				</li>
			</ul>
		</div>
	</div>

	<div class="wrap">

		<h1 class="screen-reader-text"><?php esc_html_e( 'Posterno tools' ); ?></h1>

		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( pno_get_tools_tabs() as $tab_id => $tab_name ) {
				$tab_url = add_query_arg(
					array(
						'tab' => $tab_id,
					)
				);
				$tab_url = remove_query_arg(
					array(
						'edd-message',
					),
					$tab_url
				);
				$active  = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
			}
			?>
		</h2>

		<div class="metabox-holder">

		</div>

	</div>

	<?php

}

/**
 * Get list of registered tools tabs.
 *
 * @return array
 */
function pno_get_tools_tabs() {

	$tabs = [
		'cache'  => esc_html__( 'Cache' ),
		'import' => esc_html__( 'Import' ),
		'export' => esc_html__( 'Export' ),
	];

	return apply_filters( 'pno_tools_tabs', $tabs );

}
