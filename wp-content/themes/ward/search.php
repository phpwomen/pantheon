<?php
/**
 * The template for displaying Search Results pages.
 *
 * @since 1.0.0
 */
get_header(); ?>

	<section id="primary" <?php bavotasan_primary_attr(); ?>>

		<?php if ( have_posts() ) : ?>

			<header id="archive-header">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
							<h1 class="page-title"><?php
								global $wp_query;
							    $num = $wp_query->found_posts;
								printf( '%1$s "%2$s"',
								    $num . __( ' search results for', 'ward'),
								    get_search_query()
								);
							?></h1>
						</div>
					</div>
				</div>
			</header>
			<?php
			while ( have_posts() ) : the_post();
				get_template_part( 'content', get_post_format() );
			endwhile;

			bavotasan_pagination();
		else :
			get_template_part( 'content', 'none' );
		endif;
		?>

	</section><!-- #primary.c8 -->

<?php get_footer(); ?>