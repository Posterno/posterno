<?php
/**
 * The template for displaying the dashboard welcome page.
 *
 * This template can be overridden by copying it to yourtheme/posterno/dashboard/welcome.php
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

$user_id = get_current_user_id();

$published = pno_count_published_listings( $user_id );
$pending   = pno_count_pending_listings( $user_id );
$expired   = pno_count_expired_listings( $user_id );

$class = 'col-md-6';

if ( $published && $expired && $pending ) {
	$class = 'col-md-4';
}

?>

<div class="card-deck">
	<?php if ( $published > 0 ) : ?>
		<div class="card mb-3 <?php echo esc_attr( $class ); ?> col-sm-12 pno-dashboard-count-card">
			<div class="row no-gutters">
				<div class="col-md-8">
					<div class="card-body">
						<h5 class="card-title"><?php echo absint( $published ); ?></h5>
						<p class="card-text"><?php echo esc_html( _n( 'Published listing', 'Published listings', absint( $published ), 'posterno' ) ); ?></p>
					</div>
				</div>
				<div class="col-md-4 align-self-center count-icon">
					<i class="fas fa-clipboard-list"></i>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $pending > 0 ) : ?>
	<div class="card mb-3 <?php echo esc_attr( $class ); ?> col-sm-12 pno-dashboard-count-card">
		<div class="row no-gutters">
			<div class="col-md-8">
				<div class="card-body">
					<h5 class="card-title"><?php echo absint( $pending ); ?></h5>
					<p class="card-text"><?php echo esc_html( _n( 'Pending listing', 'Pending listings', absint( $pending ), 'posterno' ) ); ?></p>
				</div>
			</div>
			<div class="col-md-4 align-self-center count-icon">
				<i class="fas fa-clock"></i>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( $expired > 0 ) : ?>
	<div class="card mb-3 <?php echo esc_attr( $class ); ?> col-sm-12 pno-dashboard-count-card">
		<div class="row no-gutters">
			<div class="col-md-8">
				<div class="card-body">
					<h5 class="card-title"><?php echo absint( $expired ); ?></h5>
					<p class="card-text"><?php echo esc_html( _n( 'Expired listing', 'Expired listings', absint( $expired ), 'posterno' ) ); ?></p>
				</div>
			</div>
			<div class="col-md-4 align-self-center count-icon">
				<i class="fas fa-eye-slash"></i>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

