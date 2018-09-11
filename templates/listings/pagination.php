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
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<nav aria-label="<?php esc_html_e( 'Pagination' ); ?>">
	<ul class="pagination pno-pagination">
		<li class="page-item disabled">
			<a class="page-link" href="#" tabindex="-1">Previous</a>
		</li>
		<li class="page-item">
			<a class="page-link" href="#">1</a>
		</li>
		<li class="page-item active">
			<a class="page-link" href="#">2 <span class="sr-only">(current)</span></a>
		</li>
		<li class="page-item">
			<a class="page-link" href="#">3</a>
		</li>
		<li class="page-item">
			<a class="page-link" href="#">Next</a>
		</li>
	</ul>
</nav>
