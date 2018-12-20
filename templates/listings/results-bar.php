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
								'total'    => $wp_query->post_count,
								'per_page' => $wp_query->query_vars['posts_per_page'],
								'current'  => $wp_query->found_posts,
							]
						)
						->get_template_part( 'listings/results', 'count' );

					?>
			</div>

			<div class="col-12 col-md-4 text-md-right">
			One of two columns
			</div>
		</div>

	</div>

</div>
