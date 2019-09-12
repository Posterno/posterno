<?php
/**
 * Displays the button to send the expired email to the listing author in the admin panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<div class="misc-pub-section">
	<a href="<?php echo esc_url( $url ); ?>" class="button"><?php esc_html_e( 'Send listing expired notification', 'posterno' ); ?></a>
</div>
