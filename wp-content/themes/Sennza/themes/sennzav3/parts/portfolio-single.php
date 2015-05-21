<?php
/**
 * @package Sennza Version 3
 */
?>

<div class="folio-wrap">
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php sz_get_the_featured_image(); ?>

	<div class="row">
		<div class="entry-content columns small-12 large-10 large-centered">

			<h1 class="entry-title">
				<?php the_title(); ?>
			</h1>

			<?php
				$project_description = get_post_meta( get_the_ID(), 'bq_project_description' );
				if ( ! ( $project_description[0] == '' ) ) { ?>

					<p><?php echo $project_description[0]; ?></p>

				<?php } else {
					the_content();
				}
			 ?>

			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'sennzaversion3' ),
					'after'  => '</div>',
				) );
			?>

			<?php
			$project_url = get_post_meta( get_the_ID(), 'bq_project_url' );
			if ( ! empty ( $project_url ) ) { ?>

				<a href="<?php echo esc_url( $project_url[0] ); ?>" class="more-link">
					<?php _e( 'Visit Site', 'sennzaversion3' ); ?>
				</a>

			<?php } ?>

		</div><!-- .entry-content -->
	</div><!-- .row -->


</article><!-- #post-## -->
</div>