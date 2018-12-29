<?php
/**
 * The template for displaying the content of listings taxonomies pages.
 * This template file is loaded only for themes that do not declare support for Posterno.
 *
 * This template can be overridden by copying it to yourtheme/pno/taxonomy.php
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

/**
 * Hook: loads before the taxonomy content and loop.
 */
do_action( 'pno_before_taxonomy_content' );

$description = term_description();

?>

<div class="pno-taxonomy-wrapper">

	<?php if ( $description ) : ?>
		<?php echo wp_kses_post( $description ); ?>
	<?php endif; ?>

	<div class="pno-listings-container">

		<?php

		if ( have_posts() ) {

			/**
			 * Hook: loads before the taxonomy & archive listings loop when listings are available.
			 */
			do_action( 'pno_before_taxonomy_loop' );

			posterno()->templates->get_template_part( 'listings/results', 'bar' );

			while ( have_posts() ) {

				the_post();

				posterno()->templates->get_template_part( 'listings/card', pno_get_listings_results_active_layout() );

			}

			posterno()->templates->get_template_part( 'listings/results', 'footer' );

			/**
			 * Hook: loads after the taxonomy & archive listings loop when listings are available.
			 */
			do_action( 'pno_after_taxonomy_loop' );

		} else {

			posterno()->templates->get_template_part( 'listings/not-found' );

		}

		?>

	</div>

</div>

<?php

/**
 * Hook: loads after the taxonomy content and loop.
 */
do_action( 'pno_after_taxonomy_content' );
