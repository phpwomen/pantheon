<?php
/**
 * The template for displaying posts in the Quote post format
 *
 * @since 1.0.0
 */
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ( ! is_single() ) { ?>
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
		<?php } ?>
			    <i class="icon-quote-left quote"></i>
			    <div class="entry-content">
				    <?php the_content( __( 'Read more &rarr;', 'ward' ) ); ?>
			    </div><!-- .entry-content -->

			    <?php get_template_part( 'content', 'footer' ); ?>
		<?php if ( ! is_single() ) { ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</article>