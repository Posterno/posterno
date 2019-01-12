<?php
/**
 * The template for displaying the content of the single listing page template.
 *
 * This template can be overridden by copying it to yourtheme/pno/single.php
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
 * Hook: triggers before the content of the single listing page is displayed.
 */
do_action( 'pno_before_single_listing' );

?>

<div class="pno-single-listing-wrapper">

	<div class="listing-featured-image">
		<?php the_post_thumbnail( 'full' ); ?>
	</div>

</div>

<?php

/**
 * Hook: triggers after the content of the single listing page is displayed.
 */
do_action( 'pno_after_single_listing' );
