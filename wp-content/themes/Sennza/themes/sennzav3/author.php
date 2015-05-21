<?php
/**
 * The template for displaying Author archive pages
 *
 * @package Sennza Version 3
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

		<div class="row">
			<header class="archive-header columns large-8 large-centered">
				<h1 class="archive-title">
					<?php

						the_post();

						printf( __( 'All posts by: %s', 'mnbv2' ), get_the_author() );
					?>
				</h1>
				<?php if ( get_the_author_meta( 'description' ) ) : ?>
				<div class="author-description">
					<p>
						<?php the_author_meta( 'description' ); ?>
					</p>
				</div>
				<?php endif; ?>
			</header><!-- .archive-header -->
		</div>

		<hr>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
					get_template_part( 'parts/content', get_post_format() );
				?>

			<?php endwhile; ?>

			<?php sz_paging_nav(); ?>

		<?php else : ?>

			<div class="row">
				<?php get_template_part( 'parts/content', 'none' ); ?>
			</div>

		<?php endif; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>