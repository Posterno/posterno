<?php
/**
 * The template for displaying the dashboard listings management page.
 *
 * This template can be overridden by copying it to yourtheme/posterno/dashboard/manage-listings.php
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

	<h2><?php esc_html_e( 'Manage listings', 'posterno' ); ?></h2>

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
					->get_template_part( 'dashboard/filter', 'statuses' );
			?>
		</div>
		<div class="col-12 col-sm-12 col-md-4 text-sm-left text-md-right">
			<?php if ( $data->submission_page && pno_can_user_submit_listings() ) : ?>
				<a class="btn btn-primary btn-sm" href="<?php echo esc_url( get_permalink( $data->submission_page ) ); ?>" role="button"><i class="fas fa-plus-circle mr-1"></i> <?php esc_html_e( 'Add listing', 'posterno' ); ?></a>
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
							<?php foreach ( $data->columns as $col_key => $col_name ) : ?>
								<?php if ( $col_key == 'name' ) : ?>
									<td>
										<?php if ( pno_is_listing_pending_approval( $listing_id ) ) : ?>
											<?php pno_the_listing_title( $listing_id ); ?>
										<?php else : ?>
											<a href="<?php echo esc_url( get_permalink( $listing_id ) ); ?>">
												<?php pno_the_listing_title( $listing_id ); ?>
											</a>
										<?php endif; ?>
									</td>
								<?php elseif ( $col_key == 'date' ) : ?>
									<td><?php pno_the_listing_publish_date( $listing_id ); ?></td>
								<?php elseif ( $col_key == 'expires' ) : ?>
									<td><?php pno_the_listing_expire_date( $listing_id ); ?></td>
								<?php elseif ( $col_key == 'status' ) : ?>
									<td>
										<?php
										if ( array_key_exists( get_post_status( $listing_id ), pno_get_listing_post_statuses() ) ) :

											$statuses       = pno_get_listing_post_statuses();
											$status_id      = get_post_status( $listing_id );
											$current_status = $statuses[ $status_id ];

											?>

											<span class="badge badge-light pno-listing-status-<?php echo esc_attr( $status_id ); ?>">
												<?php echo esc_html( $current_status ); ?>
											</span>

										<?php endif; ?>
									</td>
								<?php elseif ( $col_key == 'actions' ) : ?>
									<td>
										<?php
											posterno()->templates
												->set_template_data( [ 'id' => $listing_id ] )
												->get_template_part( 'dashboard/actions', 'list' );
										?>
									</td>
								<?php else : ?>
									<td>
										<?php

										/**
										 * Allow developers to display custom content within the dashboard listings management page.
										 *
										 * @param string $listing_id the id number of the current listing.
										 */
										do_action( "pno_listings_dashboard_table_column_{$col_key}", $listing_id );

										?>
									</td>
								<?php endif; ?>
							<?php endforeach; ?>
						</tr>
						<?php
						endforeach;
				}
				?>

				<?php wp_reset_postdata(); ?>

			<?php else : ?>
				<tr class="no-items">
					<td class="colspanchange" colspan="<?php echo count( $data->columns ); ?>">
						<?php esc_html_e( 'No listings found.', 'posterno' ); ?>
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
