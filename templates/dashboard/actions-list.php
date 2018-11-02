<?php
/**
 * The template for displaying the list of actions available for listings.
 *
 * This template can be overridden by copying it to yourtheme/pno/listings/actions-list.php
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

$actions = pno_get_listings_actions();

if ( empty( $actions ) ) {
	return;
}

?>

<div class="dropdown">
	<a class="btn btn-outline-secondary btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<i class="fas fa-ellipsis-h"></i>
	</a>
	<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
		<?php foreach ( $actions as $action_id => $action ) : ?>

			<?php if ( $action_id === 'delete' ) : ?>
				<div class="dropdown-divider"></div>
			<?php endif; ?>

			<?php

			$icon = '';

			switch ( $action_id ) {
				case 'edit':
					$icon = 'fa-pen';
					break;
				case 'view':
					$icon = 'fa-eye';
					break;
				case 'delete':
					$icon = 'fa-trash-alt';
					break;
			}

			?>

			<?php if ( $action_id === 'view' && ! pno_is_listing_pending_approval( $data->id ) ) : ?>

				<a class="dropdown-item" href="<?php echo esc_url( get_permalink( $data->id ) ); ?>">
					<?php if ( $icon ) : ?>
						<i class="fas <?php echo esc_attr( $icon ); ?> mr-2"></i>
					<?php endif; ?>
					<?php echo esc_html( $action['title'] ); ?>
				</a>

			<?php elseif ( $action_id === 'edit' ) : ?>

				<a class="dropdown-item" href="<?php echo esc_url( pno_get_listing_edit_page_url( $data->id ) ); ?>">
					<?php if ( $icon ) : ?>
						<i class="fas <?php echo esc_attr( $icon ); ?> mr-2"></i>
					<?php endif; ?>
					<?php echo esc_html( $action['title'] ); ?>
				</a>

			<?php elseif ( $action_id === 'delete' ) : ?>

				<a class="dropdown-item" data-toggle="modal" data-target="#pno-delete-listing-modal-<?php echo absint( $data->id ); ?>" href="<?php echo esc_url( pno_get_listing_action_url( $data->id, $action_id ) ); ?>">
					<?php if ( $icon ) : ?>
						<i class="fas <?php echo esc_attr( $icon ); ?> mr-2"></i>
					<?php endif; ?>
					<?php echo esc_html( $action['title'] ); ?>
				</a>

			<?php else : ?>

				<?php

				/**
				 * Allow developers to display custom content within the dashboard listings actions buttons.
				 *
				 * @param string $listing_id the id number of the current listing.
				 */
				do_action( "pno_listings_dashboard_table_column_{$action_id}", $data->id );

				?>

			<?php endif; ?>

		<?php endforeach; ?>
	</div>
</div>

<div class="modal fade" id="pno-delete-listing-modal-<?php echo absint( $data->id ); ?>" tabindex="-1" role="dialog" aria-labelledby="pno-delete-listing-title-<?php echo absint( $data->id ); ?>" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="pno-delete-listing-title-<?php echo absint( $data->id ); ?>"><?php esc_html_e( 'Delete listing' ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_html_e( 'Close' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p><?php echo sprintf( esc_html__( 'Are you sure you want to delete the "%s" listing? This action cannot be undone.' ), '<strong>' . esc_html( pno_get_the_listing_title( $data->id ) ) . '</strong>' ); ?></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Close' ); ?></button>
				<a href="<?php echo esc_url( pno_get_listing_action_url( $data->id, $action_id ) ); ?>" class="btn btn-danger"><i class="fas fa-trash-alt mr-2"></i><?php esc_html_e( 'Delete listing' ); ?></a>
			</div>
		</div>
	</div>
</div>
