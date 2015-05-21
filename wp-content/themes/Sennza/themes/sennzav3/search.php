<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Sennza Version 3
 */

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<div class="row">

				<header class="page-header large-8 large-centered columns">
					<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'sennzaversion3' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
				</header><!-- .page-header -->

			</div>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'parts/content', 'search' ); ?>

			<?php endwhile; ?>

			<?php sz_paging_nav(); ?>

		<?php else : ?>

			<?php get_template_part( 'parts/content', 'none' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php get_footer(); ?>