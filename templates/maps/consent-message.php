<?php
/**
 * The template for displaying the maps gdpr consent request message.
 *
 * This template can be overridden by copying it to yourtheme/posterno/maps/consent-message.php
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

/**
 * Filter: allow developers to modify the consent request message for maps.
 *
 * @param string $message
 * @return string
 */
$message = apply_filters( 'pno_map_cookie_message', sprintf( __( 'The embedded map on our site uses third party cookies by <a href="%s" target="_blank">Google Maps</a>. Please give consent to view the map.', 'posterno' ), 'https://policies.google.com/privacy' ) );

?>

<div id="pno-map-consent-message" class="alert alert-info" role="alert">
	<form action="<?php echo esc_url( pno_get_full_page_url() ); ?>" method="GET">
		<p><?php echo wp_kses_post( $message ); ?></p>
		<?php wp_nonce_field( 'pno_give_map_consent', 'map_consent_nonce' ); ?>
		<input type="submit" name="pno-map-consent-form" class="btn btn-primary" value="<?php esc_html_e( 'Give consent', 'posterno' ); ?>">
	</form>
</div>
