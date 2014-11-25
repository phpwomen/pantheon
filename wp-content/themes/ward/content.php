<?php
$bavotasan_theme_options = bavotasan_theme_options();
$format = get_post_format();
$featured_image = ( has_post_thumbnail() ) ? 'featured-image' : 'no-featured-image';
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( $featured_image ); ?>>
		<?php if ( ! is_single() ) { ?>
		<div class="container">
			<div class="row">
		<?php } ?>
				<?php
				$align = get_post_meta( get_the_ID(), 'bavotasan_home_page_alignment', true );
				$col = ( is_single() ) ? '' : 'col-sm-12';
				if( ! is_single() && 'excerpt' == $bavotasan_theme_options['excerpt_content'] ) {
					if ( has_post_thumbnail() ) {
						$col = 'col-md-7 col-sm-12';
						?>
						<div class="col-md-5 col-sm-12 <?php echo $align; ?>">
							<a href="<?php the_permalink(); ?>" class="image-anchor">
								<?php the_post_thumbnail( 'home-page', array( 'class' => 'aligncenter' ) ); ?>
							</a>
						</div>
						<?php
					} else {
						$col = 'col-md-8 col-sm-12 col-md-offset-2';
					}
				}
				?>

				<div class="<?php echo $col; ?>">

				    <?php get_template_part( 'content', 'header' ); ?>

				    <div class="entry-content">
					    <?php
						if ( 'excerpt' == $bavotasan_theme_options['excerpt_content'] && empty( $format ) && ( ! is_single() || is_search() || is_archive() ) ) {
							the_excerpt();
						} else {
							the_content( __( 'Read more &rarr;', 'ward' ) );
						}
						?>
				    </div><!-- .entry-content -->
				    <?php get_template_part( 'content', 'footer' ); ?>

				</div>
		<?php if ( ! is_single() ) { ?>
			</div>
		</div>
		<?php } ?>
	</article><!-- #post-<?php the_ID(); ?> -->