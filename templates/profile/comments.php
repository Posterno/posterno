<?php
/**
 * The template for displaying the comments component content on profile pages.
 *
 * This template can be overridden by copying it to yourtheme/posterno/profile/comments.php
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

$comments = pno_get_member_submitted_comments( $data->user_id );

?>

<div id="pno-profile-comments" class="mt-4">

	<?php if ( ! empty( $comments ) ) : ?>

		<ul class="list-unstyled">

			<?php foreach ( $comments as $comment ) : ?>

				<?php
					$comment_content = wp_trim_words( $comment->comment_content, $num_words = 13, $more = null );
					$the_post        = get_the_title( $comment->comment_post_ID );
					$the_permalink   = get_post_permalink( $comment->comment_post_ID );
					$the_date        = get_comment_date( get_option( 'date_format' ), $comment->comment_ID );
				?>

				<li class="media mb-4">
					<div class="media-body">
						<p>"<?php echo wp_kses_post( $comment_content ); ?>" <?php printf( wp_kses_post('on %1$s, %2$s.' ), '<a href="' . $the_permalink . '">' . $the_post . '</a>', $the_date ); ?></p>
					</div>
				</li>

			<?php endforeach; ?>

		</ul>

	<?php else : ?>

		<?php

		posterno()->templates
			->set_template_data(
				[
					'type'    => 'info',
					'message' => sprintf( esc_html__( '%s has not made any comment yet.', 'posterno' ), pno_get_user_first_name( $data->user_details ) ),
				]
			)
			->get_template_part( 'message' );

		?>

	<?php endif; ?>

</div>
