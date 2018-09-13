<?php
/**
 * The template for displaying the dashboard listings management page.
 *
 * This template can be overridden by copying it to yourtheme/pno/dashboard/manage-listings.php
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

<div class="pno-template manage-listings">

	<h2><?php esc_html_e( 'Manage listings' ); ?></h2>

	<?php

	/**
	 * Action that fires before the markup of the listings management section starts.
	 */
	do_action( 'pno_before_manage_listings' );

	?>

	<div class="row justify-content-between pno-listings-table-filter mt-4 mb-3">
		<div class="col-12 col-sm-12 col-md-4">
			<?php
				posterno()->templates
					->get_template_part( 'listings/filter', 'statuses' );
			?>
		</div>
		<div class="col-12 col-sm-12 col-md-4 text-sm-left text-md-right">
			<?php if ( $data->submission_page ) : ?>
				<a class="btn btn-primary btn-sm" href="<?php echo esc_url( get_permalink( $data->submission_page ) ); ?>" role="button"><i class="fas fa-plus-circle mr-1"></i> <?php esc_html_e( 'Add listing' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<table class="table table-bordered">
		<thead>
			<tr>
				<?php foreach ( $data->columns as $col_key => $col_name ) : ?>
					<th scope="col"><?php echo esc_html( $col_name ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php if ( $data->listings->have_posts() ) : ?>
				<?php
				$found_listings = $data->listings->get_posts();
				if ( is_array( $found_listings ) && ! empty( $found_listings ) ) {
					foreach ( $found_listings as $listing_id ) :
						?>
						<tr>
							<td>
								<a href="<?php echo esc_url( get_permalink( $listing_id ) ); ?>">
									<?php pno_the_listing_title( $listing_id ); ?>
								</a>
							</td>
							<td><?php pno_the_listing_publish_date( $listing_id ); ?></td>
							<td><?php pno_the_listing_expire_date( $listing_id ); ?></td>
							<td>
								<?php
									posterno()->templates
										->get_template_part( 'listings/actions', 'list' );
								?>
							</td>
						</tr>
						<?php
						endforeach;
				}
				?>

				<?php wp_reset_postdata(); ?>

			<?php else : ?>
				<tr class="no-items">
					<td class="colspanchange" colspan="<?php echo count( $data->columns ); ?>">
						<?php esc_html_e( 'No listings found.' ); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php
	// Display pagination.
	posterno()->templates
		->set_template_data( [ 'max_num_pages' => $data->listings->max_num_pages ] )
		->get_template_part( 'listings/pagination' );

	/**
	 * Action that fires after the markup of the listings management section starts.
	 */
	do_action( 'pno_after_manage_listings' );

	?>

</div>
