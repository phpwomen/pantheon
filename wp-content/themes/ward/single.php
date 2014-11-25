<?php
/**
 * The Template for displaying all single posts.
 *
 * @since 1.0.0
 */
get_header(); ?>

	<div id="primary" <?php bavotasan_primary_attr(); ?>>

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', get_post_format() ); ?>

			<div id="posts-pagination">
				<h3 class="screen-reader-text"><?php _e( 'Post navigation', 'ward' ); ?></h3>
				<?php if ( 'attachment' == get_post_type( get_the_ID() ) ) { ?>
					<div class="previous pull-left"><?php previous_image_link( false, __( '&larr; Previous Image', 'ward' ) ); ?></div>
					<div class="next pull-right"><?php next_image_link( false, __( 'Next Image &rarr;', 'ward' ) ); ?></div>
				<?php } else { ?>
					<div class="previous pull-left"><?php previous_post_link( '%link', __( '&larr; %title', 'ward' ) ); ?></div>
					<div class="next pull-right"><?php next_post_link( '%link', __( '%title &rarr;', 'ward' ) ); ?></div>
				<?php } ?>
			</div><!-- #posts-pagination -->


			<?php comments_template( '', true ); ?>

		<?php endwhile; // end of the loop. ?>

	</div>

<?php get_footer(); ?>