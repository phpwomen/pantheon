<?php
/**
 * Template Name: Contact Page
 *
 * A custom template to show the map above the contact page.
 *
 * @package Sennza Version 3
 */

get_header(); ?>
<div id="interchangemap" data-interchange="[<?php echo get_template_directory_uri(); ?>/parts/google-map-image.php, (small)], [<?php echo get_template_directory_uri(); ?>/parts/google-map.php, (medium)], [<?php echo get_template_directory_uri(); ?>/parts/google-map.php, (large)]">
</div>

	<div id="primary" class="content-area">
		<main id="main" class="site-main row" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'parts/content', 'page' ); ?>

			<div>

			<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>
