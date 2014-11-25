<?php
/**
 * The template for displaying posts in the Video post format.
 *
 * @since 1.0.0
 */
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ( ! is_single() ) { ?>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
		<?php } ?>
				    <?php get_template_part( 'content', 'header' ); ?>

					<div class="entry-content">
						<?php the_content( __( 'Read more &rarr;', 'ward' ) );	?>
					</div><!-- .entry-content -->

				    <?php get_template_part( 'content', 'footer' ); ?>
		<?php if ( ! is_single() ) { ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</article><!-- #post -->