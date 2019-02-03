<?php
/**
 * Displays the content of the admin getting started page.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<div class="wrap about-wrap" id="pno-getting-started">

	<h1><?php esc_html_e( 'Welcome to Posterno (Beta)' ); ?></h1>

	<div class="about-text">

		<?php echo sprintf( __('Posterno provides you the tools you need to create any kind of listings & classifieds directory. Check out the <a href="%1$s" target="_blank">plugin documentation</a> for a comprehensive introduction to your new plugin. With just a few quick clicks, you’ll be creating your directory in no time!' ), 'https://docs.posterno.com' ); ?>

	</div>

	<div class="pno-badge"><?php printf( esc_html__( 'Version %s' ), PNO_VERSION ); ?></div>

	<?php $this->tabs(); ?>

	<div class="changelog under-the-hood feature-list">

		<div class="feature-section  two-col">

			<div class="col">
				<h3></h3>
				<p></p>
			</div>

			<div class="last-feature col">
			</div>

			<hr>

			<div class="return-to-dashboard">
				<a href="https://posterno.com" target="_blank"><?php esc_html_e( 'Visit the Posterno website' ); ?></a>
			</div>

		</div>

	</div>

</div>
