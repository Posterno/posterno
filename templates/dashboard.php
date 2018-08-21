<?php
/**
 * The template for displaying the dashboard.
 *
 * This template can be overridden by copying it to yourtheme/pno/dashboard.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<div id="posterno-dashboard-wrapper">
	<div class="row">
		<div class="col-sm-3">
			<?php posterno()->templates->get_template_part( 'dashboard/navigation' ); ?>
		</div>
		<div class="col-sm-9">
			<?php

			$active_tab = get_query_var( 'dashboard_navigation_item' );

			/**
			 * Defines the key of the action that loads the content of the first
			 * dashboard tab.
			 *
			 * @since 0.1.0
			 */
			$first_tab = apply_filters( 'pno_set_first_dashboard_tab_key', 'dashboard' );

			if ( ! $active_tab || empty( $active_tab ) ) {

				/**
				 * Loads the content of the first tab for the dashboard page.
				 * By default the first tab is called "dashboard".
				 */
				do_action( "pno_dashboard_tab_content_{$first_tab}" );

			} else {

				/**
				 * Loads the content of the currently active dashboard tab.
				 */
				do_action( "pno_dashboard_tab_content_{$active_tab}" );
			}

			?>
		</div>
	</div>
</div>
