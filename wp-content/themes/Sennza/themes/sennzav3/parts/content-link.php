<?php
/**
 * @package Sennza Version 3
 */
?>
<?php $links = ( wp_extract_urls( get_the_content() ) ); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="row">
		<header class="entry-header columns large-8 large-centered">
			<h1 class="entry-title"><a href="<?php echo esc_url($links[0]); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a><span class="genericon genericon-link"></span></h1>

			<div class="entry-meta">
				<?php sz_posted_on(); ?>
			</div><!-- .entry-meta -->
		</header><!-- .entry-header -->
	</div>
	<?php sz_get_the_featured_image(); ?>

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
	</div><!-- .row -->

	<div class="row">
		<footer class="entry-meta columns large-8 large-centered">
			<?php
				/* translators: used between list items, there is a space after the comma */
				$category_list = get_the_category_list( __( ', ', 'sennzaversion3' ) );

				/* translators: used between list items, there is a space after the comma */
				$tag_list = get_the_tag_list( '', __( ', ', 'sennzaversion3' ) );

				if ( ! sz_categorized_blog() ) {
					// This blog only has 1 category so we just need to worry about tags in the meta text
					if ( '' != $tag_list ) {
						$meta_text = __( 'This entry was tagged %2$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'sennzaversion3' );
					} else {
						$meta_text = __( 'Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'sennzaversion3' );
					}

				} else {
					// But this blog has loads of categories so we should probably display them here
					if ( '' != $tag_list ) {
						$meta_text = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'sennzaversion3' );
					} else {
						$meta_text = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'sennzaversion3' );
					}

				} // end check for categories on this blog

				printf(
					$meta_text,
					$category_list,
					$tag_list,
					get_permalink()
				);
			?>

			<?php edit_post_link( __( 'Edit', 'sennzaversion3' ), '<span class="edit-link">', '</span>' ); ?>

		</footer><!-- .entry-meta -->
	</div><!-- .row -->
</article><!-- #post-## -->
<hr />