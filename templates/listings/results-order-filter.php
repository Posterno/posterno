<?php
/**
 * The template for displaying the results order filter listings loop.
 *
 * This template can be overridden by copying it to yourtheme/pno/listings/results-grid-filter.php
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

<div class="dropdown">
	<button class="btn btn-outline-secondary dropdown-toggle" type="button" id="pno-results-order-filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Dropdown
	</button>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="pno-results-order-filter">
		<button class="dropdown-item" type="button">Action</button>
		<button class="dropdown-item" type="button">Another action</button>
		<button class="dropdown-item" type="button">Something else here</button>
	</div>
</div>
