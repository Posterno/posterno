<?php
/**
 * The template for displaying the results count before the listings loop.
 *
 * This template can be overridden by copying it to yourtheme/posterno/listings/results-count.php
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

<p class="pno-result-count">
	<?php
	if ( $data->total <= $data->per_page || -1 === $data->per_page ) {
		/* translators: %d: total results */
		esc_html( printf( _n( 'Showing the single result', 'Showing all %d results', $data->total, 'posterno' ), $data->total ) ); //phpcs:ignore
	} else {
		$first = ( $data->per_page * $data->current ) - $data->per_page + 1;
		$last  = min( $data->total, $data->per_page * $data->current );
		/* translators: 1: first result 2: last result 3: total results */
		esc_html( printf( _nx( 'Showing the single result', 'Showing %1$d&ndash;%2$d of %3$d results', $data->total, 'with first and last result', 'posterno' ), $first, $last, $data->total  ) ); //phpcs:ignore
	}
	?>
</p>
