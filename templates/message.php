<?php
/**
 * The template for displaying messages.
 *
 * This template can be overridden by copying it to yourtheme/pno/message.php
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

$type = isset( $data->type ) ? esc_attr( $data->type ) : 'info';

?>

<div class="alert alert-<?php echo $type; ?>" role="alert">
	<?php echo $data->message; ?>
	<?php if ( isset( $data->dismiss ) && $data->dismiss === true ) : ?>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
	<?php endif; ?>
</div>
