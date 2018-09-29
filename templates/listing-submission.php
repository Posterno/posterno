<?php
/**
 * The template for displaying the listing submission page.
 *
 * This template can be overridden by copying it to yourtheme/pno/listing-submission.php
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

?>

<div id="pno-listing-submission">

	<?php

	if ( $data->step === 'submit_listing' ) {

		posterno()->templates
			->set_template_data(
				[
					'form'         => $data->form,
					'submit_label' => esc_html__( 'Submit listing &rarr;' ),
				]
			)
			->get_template_part( 'form' );

	} else {

		posterno()->templates
			->set_template_data(
				[
					'title'        => 'title',
					'form'         => $data->form,
					'submit_label' => esc_html__( 'Continue &rarr;' ),
				]
			)
			->get_template_part( 'forms/listing-type-selection' );

	}

	?>

</div>
