<?php
/**
 * The template for displaying the pagination of various sections of Posterno.
 *
 * This template can be overridden by copying it to yourtheme/pno/listings/pagination.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 * @package Posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$range     = 4;
$showitems = ( $range * 2 ) + 1;
$paged     = get_query_var( 'paged' );
$pages     = $data->max_num_pages;

$pagination = paginate_links(
	array(
		'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
		'total'        => $pages,
		'current'      => max( 1, $paged ),
		'format'       => '?paged=%#%',
		'show_all'     => false,
		'type'         => 'array',
		'end_size'     => 2,
		'mid_size'     => 1,
		'prev_next'    => true,
		'prev_text'    => sprintf( '<i></i> %1$s', esc_html__( 'Newer Posts', 'wp-user-manager' ) ),
		'next_text'    => sprintf( '%1$s <i></i>', esc_html__( 'Older Posts', 'wp-user-manager' ) ),
		'add_args'     => false,
		'add_fragment' => '',
	)
);

if ( empty( $pagination ) ) {
	return;
}

$custom_class = isset( $data->layout ) ? sanitize_key( $data->layout ) : false;

?>

<nav aria-label="<?php esc_html_e( 'Pagination' ); ?>">
	<ul class="pagination pno-pagination <?php echo esc_attr( $custom_class ); ?>">

		<?php if ( $paged > 2 && $paged > $range + 1 && $showitems < $pages ) : ?>
			<li class="page-item">
				<a class="page-link" href="<?php echo esc_url( get_pagenum_link( 1 ) ); ?>"><?php esc_html_e( 'First' ); ?></a>
			</li>
		<?php endif; ?>

		<?php if ( $paged > 1 && $showitems < $pages ) : ?>
			<li class="page-item">
				<a class="page-link" href="<?php echo esc_url( get_pagenum_link( $paged - 1 ) ); ?>"><?php esc_html_e( 'Previous' ); ?></a>
			</li>
		<?php endif; ?>

		<?php

		for ( $i = 1; $i <= $pages; $i++ ) {

			if ( 1 != $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {

				if ( $paged == $i ) :
					?>

					<li class="page-item active">
						<a class="page-link" href="#"><?php echo esc_html( $i ); ?> <span class="sr-only"><?php esc_html_e( '(current)' ); ?></span></a>
					</li>

				<?php else : ?>

					<li class="page-item <?php if ( $paged === 0 && $i === 1 ) : ?>active<?php endif; ?>">
						<a class="page-link" href="<?php echo esc_url( get_pagenum_link( $i ) ); ?>"><?php echo esc_html( $i ); ?></a>
					</li>

				<?php
				endif;

			}
		}

		?>

		<?php if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) : ?>
			<li class="page-item disabled">
				<a class="page-link" href="#" tabindex="-1" aria-disabled="true">...</a>
			</li>
			<li class="page-item">
				<a class="page-link" href="<?php echo esc_url( get_pagenum_link( $pages ) ); ?>"><?php echo esc_html( $pages ); ?></a>
			</li>
		<?php endif; ?>

		<?php

		if ( $paged < $pages && $showitems < $pages ) :

			$paged = $paged === 0 ? $paged + 1 : $paged;

			?>

			<li class="page-item">
				<a class="page-link" href="<?php echo esc_url( get_pagenum_link( $paged + 1 ) ); ?>"><?php esc_html_e( 'Next' ); ?></a>
			</li>

		<?php endif; ?>

	</ul>
</nav>
