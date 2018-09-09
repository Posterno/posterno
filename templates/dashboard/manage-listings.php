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

	<?php

	/**
	 * Action that fires after the markup of the listings management section starts.
	 */
	do_action( 'pno_after_manage_listings' );

	?>

</div>
