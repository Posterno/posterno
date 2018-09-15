<?php
/**
 * The template for displaying the steps of a form.
 *
 * This template can be overridden by copying it to yourtheme/pno/forms/steps.php
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

<div id="pno-listings-submission-steps" class="d-none d-sm-block">
	<ul class="nav nav-tabs nav-fill" id="pno-listings-submission-steps">
		<?php foreach ( $data->steps as $step_key => $step ) : ?>
			<li class="nav-item">
				<span class="nav-link <?php if ( $step_key === $data->active_step ) : ?>active<?php endif; ?> disabled" ><?php echo esc_html( $step['name'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

