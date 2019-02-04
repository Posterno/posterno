<?php
/**
 * The template for displaying the output of the social networks fields content in profiles or listings pages.
 *
 * This template can be overridden by copying it to yourtheme/posterno/output/social-networks-field.php
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

$socials = pno_get_registered_social_media();

?>

<div class="pno-social-networks-list">
	<ul class="list-inline m-0">

		<?php if ( is_array( $data->networks ) && ! empty( $data->networks ) ) : ?>

			<?php
			foreach ( $data->networks as $social ) :

				$network_class = $social['social_id'];

				?>

				<li class="list-inline-item">
					<a href="<?php echo esc_url( $social['social_url'] ); ?>" data-toggle="tooltip" data-placement="top" title="<?php echo esc_html( $socials[ $social['social_id'] ] ); ?>">
						<i class="fab fa-<?php echo esc_attr( $network_class ); ?>"></i>
					</a>
				</li>

			<?php endforeach; ?>

		<?php endif; ?>
	</ul>
</div>
