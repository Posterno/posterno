<?php
/**
 * The template for displaying the results footer within the listings loop.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/results-footer.php
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

<div class="pno-results-footer mt-4">

	<div class="container-fluid pl-0 pr-0">

		<div class="row justify-content-between">

			<div class="col-12 col-md-4">
				<?php posterno()->templates->get_template_part( 'listings/results', 'listings-per-page' ); ?>
			</div>

			<div class="col-12 col-md-6 text-md-right">

				<?php

				// Display pagination.
				posterno()->templates
					->set_template_data(
						[
							'max_num_pages' => $wp_query->max_num_pages,
							'layout'        => 'justify-content-end',
						]
					)
					->get_template_part( 'listings/pagination' );

				?>

			</div>

		</div>

	</div>

</div>
