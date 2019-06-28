<?php
/**
 * The template for displaying the dashboard navigation.
 *
 * This template can be overridden by copying it to yourtheme/posterno/dashboard/navigation.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$dashboard_tabs = pno_get_nav_menu_items_by_location( 'pno-dashboard-menu' );

if ( empty( $dashboard_tabs ) ) {
	$dashboard_tabs = pno_get_placeholder_dashboard_menu();
}

?>

<div class="list-group">
	<?php

	foreach ( $dashboard_tabs as $item ) :

		$icon = 'home';

		if ( ! empty( $item->pno_identifier ) ) {
			switch ( $item->pno_identifier ) {
				case 'edit-account':
					$icon = 'user-cog';
					break;
				case 'password':
					$icon = 'key';
					break;
				case 'privacy':
					$icon = 'user-lock';
					break;
				case 'logout':
					$icon = 'sign-out-alt';
					break;
				case 'listings':
					$icon = 'list-ul';
					break;
				case 'packages':
					$icon = 'box-open';
					break;
			}
		} else {
			$icon = implode( ' ', $item->classes );
		}

		?>
		<a href="<?php echo esc_url( $item->url ); ?>" <?php pno_dashboard_navigation_item_class( $item->post_name, $item ); ?>>
			<i class="fas fa-<?php echo esc_attr( $icon ); ?> mr-2"></i>
			<?php echo esc_html( $item->title ); ?>
		</a>
	<?php endforeach; ?>
</div>
