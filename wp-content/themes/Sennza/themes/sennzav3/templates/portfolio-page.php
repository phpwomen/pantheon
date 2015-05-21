<?php
/**
 * Template Name: Portfolio Page
 *
 * @package Sennza Version 3
 */

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

// WP_Query arguments
$args = array (
	'post_type'              => 'folio',
	'post_status'            => 'publish',
	'posts_per_page'         => 5,
	'paged'                  => $paged,

);

// The Query
$folio_query = new WP_Query( $args );

get_header(); ?>

	<header class="entry-header row">
		<h1 class="entry-title columns large-12 large-centered">
			<?php the_title(); ?>
		</h1>
	</header><!-- .entry-header -->

	<div class="row">
		<div class="entry-content columns large-12 large-centered">
			<?php the_content(); ?>
		</div><!-- .entry-content -->
	</div>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">



		<?php if ( $folio_query->have_posts() ) : ?>

			<?php while ( $folio_query->have_posts() ) : $folio_query->the_post(); ?>

				<?php get_template_part( 'parts/portfolio', 'single' ); ?>

			<?php endwhile; // end of the loop. ?>


			<?php if ( is_paged() ) : ?>

			<div class="row pagination-wrap">
				<?php
					sz_pagination( $folio_query );
				?>
			</div>

			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

		<?php endif ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>
