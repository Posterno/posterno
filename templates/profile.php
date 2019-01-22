<?php
/**
 * The template for displaying members profiles.
 *
 * This template can be overridden by copying it to yourtheme/pno/profile.php
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

// Retrieve assigned user id.
$user_id = isset( $data->user_id ) ? absint( $data->user_id ) : pno_get_queried_user_id();

$user_details          = get_userdata( $user_id );
$name                  = pno_get_user_fullname( $user_details );
$description           = get_user_meta( $user_id, 'description', true );
$navigation_items      = pno_get_nav_menu_items_by_location( 'pno-profile-menu' );
$currently_active_item = pno_get_profile_currently_active_component( $navigation_items );
$members_allowed = pno_get_option( 'profiles_allow_members', false );

// If members can't see other member's profiles other than their own, then show a message.
if ( ! $members_allowed && absint( get_current_user_id() ) !== $user_id ) {

	posterno()->templates
		->set_template_data(
			[
				'type' => 'warning',
				'message' => esc_html__( 'You are not authorized to view the content of this page.' )
			]
		)
		->get_template_part( 'message' );

	return;

}

?>

<div id="pno-profile-wrapper">

	<div class="row">

		<div class="col-sm-3">

			<div class="card">

				<?php

					/**
					 * Hook: triggers before displaying the user's avatar on the profile page.
					 *
					 * @param string $user_id the user being displayed.
					 * @param WP_User $user_details some details about the user.
					 */
					do_action( 'pno_before_profile_avatar', $user_id, $user_details );

					echo get_avatar( $user_id, 200, null, null, [ 'class' => 'card-img-top' ] );

					/**
					 * Hook: triggers after displaying the user's avatar on the profile page.
					 *
					 * @param string $user_id the user being displayed.
					 * @param WP_User $user_details some details about the user.
					 */
					do_action( 'pno_after_profile_avatar', $user_id, $user_details );

				?>

				<div class="card-body text-sm-left text-md-center">

					<?php

						/**
						 * Hook: triggers before displaying the user's card content on the profile page.
						 *
						 * @param string $user_id the user being displayed.
						 * @param WP_User $user_details some details about the user.
						 */
						do_action( 'pno_before_profile_card_content', $user_id, $user_details );

					?>

					<?php if ( $name ) : ?>
						<h5 class="card-title mb-1"><?php echo esc_html( $name ); ?></h5>
					<?php endif; ?>

					<span class="badge badge-light"><?php printf( esc_html__( 'Member since: %s' ), esc_html( pno_get_user_registration_date( $user_details ) ) ); ?></span>

					<?php if ( $description ) : ?>
						<p class="card-text mt-2"><?php echo wp_kses_post( $description ); ?></p>
					<?php endif; ?>

					<?php

						/**
						 * Hook: triggers after displaying the user's card content on the profile page.
						 *
						 * @param string $user_id the user being displayed.
						 * @param WP_User $user_details some details about the user.
						 */
						do_action( 'pno_after_profile_card_content', $user_id, $user_details );

					?>

				</div>
			</div>

		</div>

		<div class="col-sm-9">

			<?php if ( $navigation_items && is_array( $navigation_items ) ) : ?>
				<ul class="nav nav-tabs pno-profile-page-navigation">
					<?php foreach ( $navigation_items as $nav_item ) : ?>
						<li class="nav-item">
							<a class="nav-link <?php if ( $currently_active_item === $nav_item->post_name ) : ?>active<?php endif; ?>" href="<?php echo esc_url( pno_get_current_profile_component_url( $nav_item->post_name ) ); ?>"><?php echo esc_html( $nav_item->post_title ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php

				/**
				 * Hook: loads the currently active profile page component by using the active component slug.
				 * Eg: pno_profile_content_slot_{$slug} where {slug} is the slug of the active component.
				 *
				 * If the component is called "about", the action would be pno_profile_content_slot_about.
				 *
				 * @param string $user_id the user being displayed.
				 * @param WP_User $user_details some details about the user.
				 */
				do_action( "pno_profile_content_slot_{$currently_active_item}", $user_id, $user_details );

			?>

		</div>

	</div>

</div>
