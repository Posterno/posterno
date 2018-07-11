<?php
/**
 * The template for displaying the dashboard navigation.
 *
 * This template can be overridden by copying it to yourtheme/pno/dashboard/navigation.php
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

$dashboard_tabs = pno_get_dashboard_navigation_items();

?>

<div class="list-group">
	<?php foreach ( $dashboard_tabs as $key => $item ) : ?>
		<a href="<?php echo pno_get_dashboard_navigation_item_url( $key ); ?>" <?php pno_dashboard_navigation_item_class( $key, $item ); ?>><?php echo esc_html( $item['name'] ); ?></a>
	<?php endforeach; ?>
</div>
