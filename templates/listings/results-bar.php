<?php
/**
 * The template for displaying the results count and sorting options before the listings loop.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/results-bar.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.1.0
 * @package posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $wp_query;

$the_query = $wp_query;

if ( isset( $data->custom_query ) && ! empty( $data->custom_query ) ) {
	$the_query = $data->custom_query;
}

?>

<div class="pno-results-bar mb-4">

	<div class="container-fluid pl-0 pr-0">

		<div class="row justify-content-between">

			<div class="col-12 col-md-4">
				<?php

					posterno()->templates
						->set_template_data(
							[
								'total'    => absint( $the_query->found_posts ),
								'per_page' => absint( $the_query->query_vars['posts_per_page'] ),
								'current'  => absint( max( 1, $the_query->get( 'paged', 1 ) ) ),
							]
						)
						->get_template_part( 'listings/results', 'count' );

					?>
			</div>

			<div class="col-12 col-md-6 text-md-right">

				<div class="row">
					<?php if ( ! pno_get_option( 'disable_layout_switcher' ) ) : ?>
					<div class="col">
						<?php posterno()->templates->get_template_part( 'listings/results', 'grid-filter' ); ?>
					</div>
					<?php endif; ?>
					<?php if ( ! pno_get_option( 'disable_sorter' ) ) : ?>
					<div class="col">
						<?php posterno()->templates->get_template_part( 'listings/results', 'order-filter' ); ?>
					</div>
					<?php endif; ?>
				</div>

			</div>
		</div>

	</div>

</div>
