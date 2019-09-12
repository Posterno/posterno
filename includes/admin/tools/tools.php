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
			<h3><?php esc_html_e( 'Posterno tools', 'posterno' ); ?></h3>
			<ul class="title-links hidden-sm-and-down">
				<li>
					<a href="https://posterno.com/extensions" rel="nofollow" target="_blank" class="page-title-action"><?php esc_html_e( 'Extensions', 'posterno' ); ?></a>
				</li>
				<li>
					<a href="https://docs.posterno.com/" rel="nofollow" target="_blank" class="page-title-action"><?php esc_html_e( 'Documentation', 'posterno' ); ?></a>
				</li>
			</ul>
		</div>
	</div>

	<div class="wrap">

		<h1 class="screen-reader-text"><?php esc_html_e( 'Posterno tools', 'posterno' ); ?></h1>

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
						'pno-tool-updated',
						'sl_activation',
						'message',
						'sl_deactivated',
					),
					$tab_url
				);
				$active  = $active_tab === $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
			}
			?>
		</h2>

		<div class="metabox-holder">
			<?php do_action( "pno_tools_{$active_tab}" ); ?>
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
		'cache'    => esc_html__( 'Cache', 'posterno' ),
		'import'   => esc_html__( 'Import', 'posterno' ),
		'export'   => esc_html__( 'Export', 'posterno' ),
		'database' => esc_html__( 'Database', 'posterno' ),
	];

	/**
	 * Filter: allows developers to adjust the tabs available for the tools page.
	 *
	 * @param array $tabs the list of tabs.
	 * @return array
	 */
	return apply_filters( 'pno_tools_tabs', $tabs );

}
