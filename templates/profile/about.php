<?php
/**
 * The template for displaying the about component content on profile pages.
 *
 * This template can be overridden by copying it to yourtheme/pno/profile/about.php
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

$profile_fields = pno_get_public_profile_fields();

?>

<div id="pno-profile-about" class="mt-4">

	<?php if ( ! empty( $profile_fields ) ) : ?>

		<ul class="list-group">

			<?php
			foreach ( $profile_fields as $field ) :

				$value = true;

				if ( ! $value ) {
					continue;
				}

				?>
				<li class="list-group-item">
					<span class="field-name"><?php echo esc_html( $field['name'] ); ?></span>:
					<?php echo $value; //phpcs:ignore ?>
				</li>
			<?php endforeach; ?>

		</ul>

	<?php endif; ?>

</div>
