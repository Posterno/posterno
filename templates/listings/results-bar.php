<?php
/**
 * The template for displaying the results count and sorting options before the listings loop.
 *
 * This template can be overridden by copying it to yourtheme/pno/listings/results-bar.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 * @package posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $wp_query;

?>

<div class="pno-results-bar">

	<div class="container-fluid pl-0 pr-0">

		<div class="row justify-content-between">

			<div class="col-12 col-md-4">
				<?php

					posterno()->templates
						->set_template_data(
							[
								'total'    => absint( $wp_query->found_posts ),
								'per_page' => absint( $wp_query->query_vars['posts_per_page'] ),
								'current'  => absint( max( 1, $wp_query->get( 'paged', 1 ) ) ),
							]
						)
						->get_template_part( 'listings/results', 'count' );

					?>
			</div>

			<div class="col-12 col-md-4 text-md-right">

				<div class="row">
					<div class="col">
						<?php posterno()->templates->get_template_part( 'listings/results', 'grid-filter' ); ?>
					</div>
					<div class="col">
						<?php posterno()->templates->get_template_part( 'listings/results', 'order-filter' ); ?>
					</div>
				</div>

			</div>
		</div>

	</div>

</div>
