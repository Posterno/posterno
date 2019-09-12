<?php
/**
 * Displays the content of the admin getting started page.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$email_address = pno_get_user_email( get_current_user_id() );
$first_name    = pno_get_user_first_name( get_current_user_id() );

?>

<div class="wrap about-wrap" id="pno-getting-started">

	<h1><?php esc_html_e( 'Welcome to Posterno', 'posterno' ); ?></h1>

	<div class="about-text">

		<?php echo sprintf( __( 'Posterno provides you the tools you need to create any kind of listings & classifieds directory. Check out the <a href="%1$s" target="_blank" rel="nofollow">plugin documentation</a> for a comprehensive introduction to your new plugin.', 'posterno' ), 'https://docs.posterno.com' ); ?>

	</div>

	<ul class="social-links">
		<li>
			<a href="https://twitter.com/posternowp?ref_src=twsrc%5Etfw" class="twitter-follow-button" data-show-count="false">Follow @posternowp</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
		</li>
		<li>
			<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fposterno%2F&width=61&layout=button_count&action=like&size=small&show_faces=false&share=false&height=21&appId=1396075753957705" width="61" height="21" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"></iframe>
		</li>
	</ul>

	<div class="pno-badge"><?php printf( esc_html__( 'Version %s', 'posterno' ), esc_attr( PNO_VERSION ) ); ?></div>

	<?php $this->tabs(); ?>

	<p class="about-description"><?php esc_html_e( 'With just a few quick clicks, you’ll be creating your directory in no time! ', 'posterno' ); ?></p>

	<div id="welcome-panel" class="welcome-panel" style="padding-top:0px;">
		<div class="welcome-panel-content">
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<h4><?php esc_html_e( 'Configure Posterno', 'posterno' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( admin_url( 'options-general.php?page=posterno-options' ) ); ?>" class="welcome-icon dashicons-admin-generic" target="_blank"><?php esc_html_e( 'Posterno options panel', 'posterno' ); ?></a></li>
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pno_emails' ) ); ?>" class="welcome-icon dashicons-email-alt" target="_blank"><?php esc_html_e( 'Setup email notifications', 'posterno' ); ?></a></li>
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=listings' ) ); ?>" class="welcome-icon dashicons-plus-alt" target="_blank"><?php esc_html_e( 'Add listings', 'posterno' ); ?></a></li>
					</ul>
				</div>
				<div class="welcome-panel-column">
					<h4><?php esc_html_e( 'Customize fields', 'posterno' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=listings&page=posterno-custom-listings-fields' ) ); ?>" class="welcome-icon dashicons-admin-settings" target="_blank"><?php esc_html_e( 'Customize listings fields', 'posterno' ); ?></a></li>
						<li><a href="<?php echo esc_url( admin_url( 'users.php?page=posterno-custom-profile-fields' ) ); ?>" class="welcome-icon dashicons-admin-users" target="_blank"><?php esc_html_e( 'Customize profile fields', 'posterno' ); ?></a></li>
						<li><a href="<?php echo esc_url( admin_url( 'users.php?page=posterno-custom-registration-form' ) ); ?>" class="welcome-icon dashicons-groups" target="_blank"><?php esc_html_e( 'Customize registration form', 'posterno' ); ?></a></li>
					</ul>
				</div>
				<div class="welcome-panel-column welcome-panel-last">
					<h4><?php esc_html_e( 'Documentation', 'posterno' ); ?></h4>
					<p class="welcome-icon welcome-learn-more"><?php echo sprintf( __( 'Looking for help? <a href="%s" target="_blank" rel="nofollow">Posterno documentation</a> has got you covered.', 'posterno' ), 'https://docs.posterno.com' ); ?> <br/><br/><a href="https://docs.posterno.com" class="button" target="_blank"><?php esc_html_e( 'Read documentation', 'posterno' ); ?></a></p>
				</div>
			</div>
		</div>
	</div>

	<div class="changelog under-the-hood feature-list">

		<div class="feature-section  two-col">

			<div class="col">
				<h3><?php esc_html_e( 'Looking for help ?', 'posterno' ); ?></h3>
				<p><?php echo sprintf( __( 'If you have a question, issue or bug with the Posterno please <a href="%1$s" target="_blank">open a topic in the support forum</a>. Make sure you <a href="%2$s" target="_blank" rel="nofollow">read the documentation</a> first. We also welcome your feedback and feature requests.', 'posterno' ), 'https://wordpress.org/support/plugin/posterno/', 'https://docs.posterno.com' ); ?></p>
			</div>

			<div class="last-feature col">
				<h3><?php esc_html_e( 'Don\'t miss out', 'posterno' ); ?></h3>
				<p><?php esc_html_e( 'Be sure to sign up for the Posterno newsletter below to stay informed of important updates, news and offers. We don\'t spam and you can unsubscribe at any time.', 'posterno' ); ?></p>

				<form action="https://posterno.us5.list-manage.com/subscribe/post?u=e68e0bb69f2cdf2dfd083856c&amp;id=3368f62228" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
					<table class="form-table pno-newsletter-form">
						<tbody>
							<tr valign="middle">
								<td>
									<input type="email" value="<?php echo esc_html( $email_address ); ?>" placeholder="<?php esc_html_e( 'Email address*', 'posterno' ); ?>" name="EMAIL" class="required email" required id="mce-EMAIL">
								</td>
								<td>
									<div class="mc-field-group">
										<input type="text" value="<?php echo esc_html( $first_name ); ?>" name="FNAME" class="" id="mce-FNAME" placeholder="<?php esc_html_e( 'First name', 'posterno' ); ?>">
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div id="mce-responses" class="clear">
										<div class="response" id="mce-error-response" style="display:none"></div>
										<div class="response" id="mce-success-response" style="display:none"></div>
									</div>
									<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
									<div style="position: absolute; left: -5000px;" aria-hidden="true">
										<input type="text" name="b_e68e0bb69f2cdf2dfd083856c_3368f62228" tabindex="-1" value="">
									</div>
									<div class="clear">
										<input type="submit" value="<?php echo esc_html__( 'Subscribe', 'posterno' ); ?>" name="subscribe" id="mc-embedded-subscribe" class="button">
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>

			<hr>

			<div class="return-to-dashboard">
				<a href="https://posterno.com" rel="nofollow" target="_blank"><?php esc_html_e( 'Visit the Posterno website to find out more', 'posterno' ); ?> &rarr;</a>
			</div>

		</div>

	</div>

</div>
