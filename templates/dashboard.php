<?php
/**
 * The template for displaying the dashboard.
 *
 * This template can be overridden by copying it to yourtheme/pno/dashboard.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

?>

<div id="posterno-dashboard-wrapper">
	<div class="row">
		<div class="col-sm-4">
			<?php posterno()->templates->get_template_part( 'dashboard/navigation' ); ?>
		</div>
		<div class="col-sm-8">
			One of three columns
		</div>
	</div>
</div>
