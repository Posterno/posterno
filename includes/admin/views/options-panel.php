<?php
/**
 * Registers all the actions for the administration.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$container_id = $this->get_id();
if ( ! isset( $container_css_class ) ) {
	$container_css_class = 'theme-options';
}

$options_panel_tabs = pno_get_registered_settings_tabs();
$active_page        = isset( $_GET['page'] ) ? pno_get_string_between( esc_attr( $_GET['page'] ), '[', ']' ) : false;

?>

<div class="pno-admin-title-area">
	<div class="wrap">
		<h1><?php echo esc_html( $this->title ); ?></h1>
		<ul class="title-links hidden-sm-and-down">
			<!--<li>
				<a href="https://posterno.com/addons" target="_blank" class="page-title-action"><?php esc_html_e( 'View Addons', 'posterno' ); ?></a>
			</li>-->
			<li>
				<a href="https://docs.posterno.com/" target="_blank" class="page-title-action"><?php esc_html_e( 'Documentation', 'posterno' ); ?></a>
			</li>
		</ul>
	</div>
</div>

<div class="posterno-options-panel wrap carbon-<?php echo esc_attr( $container_css_class ); ?>">

	<h2 class="nav-tab-wrapper pno-nav-tab-wrapper">
		<?php
		foreach ( $options_panel_tabs as $option_page_id => $option_page_label ) :

			$url = esc_url( admin_url( "admin.php?page=posterno-options[{$option_page_id}]" ) );

			if ( $option_page_id === 'general' ) {
				$url = esc_url( admin_url( 'options-general.php?page=posterno-options' ) );
			}

			$active = false;

			if ( $active_page === $option_page_id && $option_page_id !== 'general' ) {
				$active = 'nav-tab-active';
			} elseif ( ! $active_page && $option_page_id === 'general' ) {
				$active = 'nav-tab-active';
			}

			?>
			<a href="<?php echo esc_url( $url ); ?>" class="nav-tab <?php echo esc_attr( $active ); ?>"><?php echo esc_html( $option_page_label ); ?></a>
		<?php endforeach; ?>
	</h2>

	<?php if ( $this->errors ) : ?>
		<div class="carbon-wp-notice notice-error">
			<?php foreach ( $this->errors as $error ) : ?>
				<p><strong><?php echo esc_html( $error ); ?></strong></p>
			<?php endforeach ?>
		</div>
	<?php elseif ( $this->notifications ) : ?>
		<?php foreach ( $this->notifications as $notification ) : ?>
			<div class="carbon-wp-notice notice-success">
				<p><strong><?php echo esc_html( $notification ); ?></strong></p>
			</div>
		<?php endforeach ?>
	<?php endif; ?>

	<form method="post" id="<?php echo esc_attr( $container_css_class ); ?>-form" enctype="multipart/form-data" action="">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">

					<?php do_action( "{$container_id}_before_fields" ); ?>

					<div class="postbox carbon-box" id="<?php echo $this->get_id(); ?>">
						<fieldset class="inside <?php echo esc_attr( $container_css_class ); ?>-container carbon-grid container-<?php echo $this->get_id(); ?>"></fieldset>
					</div>

					<?php do_action( "{$container_id}_after_fields" ); ?>
				</div>

				<div id="postbox-container-1" class="postbox-container">

					<?php do_action( "{$container_id}_before_sidebar" ); ?>

					<div id="submitdiv" class="postbox">
						<h3><?php _e( 'Actions', 'posterno' ); ?></h3>

						<div id="major-publishing-actions">

							<div id="publishing-action">
								<span class="spinner"></span>

								<?php
									$filter_name  = 'carbon_fields_' . str_replace( '-', '_', sanitize_title( $this->title ) ) . '_button_label';
									$button_label = apply_filters( $filter_name, __( 'Save Changes', 'posterno' ) );
								?>

								<input type="submit" value="<?php echo esc_html( $button_label ); ?>" name="publish" id="publish" class="button button-primary button-large">
							</div>

							<div class="clear"></div>
						</div>
					</div>

					<?php do_action( "{$container_id}_after_sidebar" ); ?>

				</div>
			</div>
		</div>
	</form>
</div>
