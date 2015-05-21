<?php
/**
 * The template used for displaying case studies
 *
 * @package Sennza Version 3
 */
?>

<?php sz_get_the_featured_image(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="row">
		<div class="entry-content columns large-8 large-centered">
			<?php the_content(); ?>
			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'sennzaversion3' ),
					'after'  => '</div>',
				) );
			?>
		</div><!-- .entry-content -->
		<?php edit_post_link( __( 'Edit', 'sennzaversion3' ), '<footer class="entry-meta row"><span class="edit-link columns large-8">', '</span></footer>' ); ?>
	</div><!-- .row -->
</article><!-- #post-## -->