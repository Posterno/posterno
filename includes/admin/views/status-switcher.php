<?php
/**
 * Displays the content of the listing status switcher in the admin panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<div class="misc-pub-section pno-status-updater">
	<table class="widefat">
		<tbody>
			<?php foreach ( $this->form->getFields() as $field ) : ?>
			<tr>
				<th scope="row">
					<?php if ( ! empty( $field->getLabel() ) ) : ?>
						<label for="<?php echo esc_attr( $field->getName() ); ?>"><?php echo esc_html( $field->getLabel() ); ?></label>
					<?php endif; ?>
				</th>
				<td>
					<?php echo $field->render(); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php wp_nonce_field( 'pno_change_listing_status', 'pno_set_listing_status' ); ?>
	<input type="submit" class="button" value="<?php esc_html_e( 'Update status' ); ?>">
</div>
