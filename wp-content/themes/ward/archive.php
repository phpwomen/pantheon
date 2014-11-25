<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each specific one.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
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
							<h1 class="page-title">
								<?php if ( is_category() ) : ?>
									<?php echo single_cat_title( '', false ); ?>
								<?php elseif ( is_author() ) : ?>
									<?php printf( __( 'Author Archive for %s', 'ward' ), get_the_author_meta( 'display_name', get_query_var( 'author' ) ) ); ?>
								<?php elseif ( is_tag() ) : ?>
									<?php printf( __( 'Tag Archive for %s', 'ward' ), single_tag_title( '', false ) ); ?>
								<?php elseif ( is_day() ) : ?>
									<?php printf( __( 'Daily Archives: %s', 'ward' ), get_the_date() ); ?>
								<?php elseif ( is_month() ) : ?>
									<?php printf( __( 'Monthly Archives: %s', 'ward' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'ward' ) ) ); ?>
								<?php elseif ( is_year() ) : ?>
									<?php printf( __( 'Yearly Archives: %s', 'ward' ), get_the_date( _x( 'Y', 'yearly archives date format', 'ward' ) ) ); ?>
								<?php else : ?>
									<?php _e( 'Blog Archives', 'ward' ); ?>
								<?php endif; ?>
							</h1><!-- .page-title -->
							<?php
							$description = term_description();
							if ( is_author() )
								$description = get_the_author_meta( 'description' );

			                if ( $description )
								printf( '<h2 class="archive-meta">%s</h2>', $description );
							?>
						</div>
					</div>
				</div>
			</header><!-- #archive-header -->

			<?php
			while ( have_posts() ) : the_post();

				/* Include the post format-specific template for the content. If you want to
				 * this in a child theme then include a file called called content-___.php
				 * (where ___ is the post format) and that will be used instead.
				 */
				get_template_part( 'content', get_post_format() );

			endwhile;

			bavotasan_pagination();
		else :
			get_template_part( 'content', 'none' );
		endif;
		?>

	</section><!-- #primary.c8 -->

<?php get_footer(); ?>