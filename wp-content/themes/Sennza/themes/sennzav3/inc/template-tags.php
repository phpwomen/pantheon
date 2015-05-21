<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package sz
 */

if ( ! function_exists( 'sz_post_nav' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 *
 * @return void
 */
function sz_post_nav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous ) {
		return;
	}
	?>
	<nav class="navigation post-navigation row" role="navigation">
		<h1 class="screen-reader-text"><?php _e( 'Post navigation', 'sennzaversion3' ); ?></h1>
		<div class="nav-links large-12 columns">
			<?php if ( function_exists( 'wp_pagenavi' ) ) { ?>
				<?php wp_pagenavi(); ?>
			<?php } else { ?>
				<?php previous_post_link( '%link', _x( '<span class="page-left">%title</span>', 'Previous post link', 'sennzaversion3' ) ); ?>
				<?php next_post_link(     '%link', _x( '<span class="page-right">%title</span>', 'Next post link',     'sennzaversion3' ) ); ?>
			<?php } ?>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif; // sz_post_nav


if ( ! function_exists( 'sz_paging_nav' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @return void
 */
function sz_paging_nav() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}
	?>
	<nav class="navigation paging-navigation row collapse" role="navigation">
		<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'sennzaversion3' ); ?></h1>
		<div class="nav-links">

			<?php if ( function_exists( 'wp_pagenavi' ) ) { ?>
				<?php sz_pagination(); ?>
			<?php } else { ?>

				<?php if ( get_next_posts_link() ) : ?>
				<div class="nav-previous small-6 columns left"><?php next_posts_link( __( '<div class="left"><i class="fa fa-chevron-left"></i>Older posts</div>', 'sennzaversion3' ) ); ?></div>
				<?php endif; ?>

				<?php if ( get_previous_posts_link() ) : ?>
				<div class="nav-next small-6 columns right text-right"><?php previous_posts_link( __( '<div class="right">Newer posts<i class="fa fa-chevron-right"></i></div>', 'sennzaversion3' ) ); ?></div>
				<?php endif; ?>
			<?php } ?>

		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif; // sennza_paging_nav


/**
 * Add a custom function to handle the navigation
 */
function sz_pagination( $custom_query = null ){
	global $wp_query, $paged;

	if ( ! is_null( $custom_query ) ){
		$wp_query = $custom_query;
	}

	if ( function_exists( 'wp_pagenavi' ) ) { ?>
		<ul class="paging">
			<li class="left">
				<?php $previous = get_previous_posts_link( __( '<i class="fa fa-chevron-left"></i>', 'sennzaversion3' ), $wp_query->max_num_pages ); ?>
				<?php if (strlen( $previous) > 0 ): ?>
					<?php echo $previous; ?>
				<?php else: ?>
					<span class="inactive"><i class="fa fa-chevron-left"></i></span>
				<?php endif; ?>
			</li>
			<li><?php wp_pagenavi( array( 'query' => $wp_query, 'paged' => $paged ) ); ?></li>
			<li class="right">
				<?php $next = get_next_posts_link( __( '<i class="fa fa-chevron-right"></i>', 'sennzaversion3' ), $wp_query->max_num_pages ); ?>
				<?php if (strlen( $next) > 0 ): ?>
					<?php echo $next; ?>
				<?php else: ?>
					<span class="inactive"><i class="fa fa-chevron-right"></i></span>
				<?php endif; ?>
			</li>
	</ul>
<?php wp_reset_query(); ?>
<?php
	}
	else {
		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'sennzaversion3' ),
			'after'  => '</div>',
		) );
	}
}


if ( ! function_exists( 'sz_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function sz_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

	<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
		<div class="comment-body">
			<?php _e( 'Pingback:', 'sennzaversion3' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'sennzaversion3' ), '<span class="edit-link">', '</span>' ); ?>
		</div>

	<?php else : ?>

	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
		<div class="large-12 comment-wrapper columns">
			<article id="div-comment-<?php comment_ID(); ?>" class="comment-body large-12 large-centered">
				<footer class="comment-meta columns large-2">
					<div class="comment-author vcard">
						<div class="gravatar-wrapper">
							<a href="<?php comment_author_url(); ?>" title="<?php comment_author(); ?>">
								<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
							</a>
						</div>
					</div><!-- .comment-author -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'sennzaversion3' ); ?></p>
					<?php endif; ?>
				</footer><!-- .comment-meta -->

				<div class="columns large-10">
					<div class="author-meta">
						<p>
							<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
								<time datetime="<?php comment_time( 'c' ); ?>">
									<?php printf( _x( '%1$s', '1: date' ), get_comment_date() ); ?>
								</time>
							</a> by <a href="<?php comment_author_url(); ?>" title="<?php comment_author(); ?>"><?php comment_author(); ?></a>
						</p>
					</div>
					<span class="comment-content">
						<?php comment_text(); ?>
					</span><!-- .comment-content -->

					<?php
						comment_reply_link( array_merge( $args, array(
							'add_below' => 'div-comment',
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'before'    => '<div class="reply">',
							'after'     => '</div>',
						) ) );
					?>


					<div class="comment-metadata">
						<?php edit_comment_link( __( 'Edit', 'sennzaversion3' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .comment-metadata -->

				</div>
			</article><!-- .comment-body -->
		</div>

	<?php
	endif;
}
endif; // ends check for sz_comment()


if ( ! function_exists( 'sz_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function sz_posted_on() {

	if ( ! post_password_required() && ( '0' != get_comments_number() ) ) {
		if ( get_comments_number() > '10' ) {
			echo "<span class='comment-count more-than-10-comments'><a href='" . get_comments_link() ."' title='Leave a comment'>" . get_comments_number() . "</a></span>";
		}
		else {
			echo "<span class='comment-count'><a href='" . get_comments_link() ."' title='Leave a comment'>" . get_comments_number() . "</a></span>";
		}
	}

	$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	printf( __( '<span class="posted-on">This entry was posted in %1$s</span><span class="byline"> by %2$s</span>', 'sennzaversion3' ),
		sprintf( '%1$s',
			$time_string
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		)
	);
}
endif;

/**
 * Returns true if a blog has more than 1 category
 */
function sz_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so _s_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so _s_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in sz_categorized_blog
 */
function sz_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'sz_category_transient_flusher' );
add_action( 'save_post',     'sz_category_transient_flusher' );

function sz_get_the_featured_image() { ?>
	<?php
	if ( has_post_thumbnail() ) { ?>
		<div class="featured-image">
		<?php
		if ( is_single() || ( is_page() && ! is_page_template( 'templates/page-case-study.php' ) ) ) {
			the_post_thumbnail( 'responsive-retina', array( 'class' => 'full-width' ) );
		}
		else {
			the_post_thumbnail( 'case-study-featured-image' );
		} ?>
		</div>
<?php	} ?>
<?php
}

/**
 * Add a no-header-margin class to certain page templates
 */

function sz_no_margin_header( $classes ) {

	if ( is_page_template( 'templates/page-case-study.php' ) or is_page_template( 'templates/page-contact.php' ) ) {
		$classes[] = 'no-header-margin';
	}

	return $classes;
}

add_filter( 'body_class', 'sz_no_margin_header' );

/**
 * Build a link for the Call To Action Section on the Home Page
 *
 * @param $page_id
 *
 * @return string
 */
function sz_get_the_cta( $page_id ){
	// Get the Call To Action Data we need
	$cta_text = get_post_meta( $page_id, 'cta-text', true );
	$cta_link = get_post_meta( $page_id, 'cta-link', true );
	$call_to_action = "<div class='text-center'><a href='" . get_permalink( $cta_link ) . "' title='" . esc_attr( $cta_text ) ."' class='button sennza-intro-button'>" . esc_attr( $cta_text ) . "</a></div>";

	return $call_to_action;
}

/**
 * Echo the Call To Action Link On The Home Page
 *
 * @param $page_id
 */

function sz_the_cta( $page_id ){
	echo sz_get_the_cta( $page_id );
}

/**
 *
 * Builds and unordered list of clients for the homepage or returns false if there aren't any clients
 *
 * @param $page_id
 *
 * @return bool|string
 */

function sz_get_the_clients( $page_id ){
	$clients = get_post_meta( $page_id, 'clients', false );
	if ( $clients ) {
		$client_list = "\n<ul class='small-block-grid-1 medium-block-grid-2 large-block-grid-3'>\n";
		foreach ( $clients as $client ) {
			$client_logo = false;
			$case_study_link = false;
			$case_study_text = false;
			$client_list .= "\t<li class='text-center'>";
			if ( $client['client-logo'] ) {
				$client_logo = wp_get_attachment_image( $client['client-logo'], 'full' );
			}
			if ( $client['case-study-link'] ) {
				$case_study_link = get_permalink( $client['case-study-link'] );
			}
			if ( $client['case-study-text'] ) {
				$case_study_text = $client['case-study-text'];
			}
			if ( $client_logo &&  $case_study_link && $case_study_text ) {
				// Build link, title and image
				$client_list .= "<a href='" . $case_study_link . "' title='" . $case_study_text . "'>" . $client_logo . "</a>";
			}
			elseif ( $client_logo && $case_study_text ) {
				// Build image with alt text
				$client_list .= wp_get_attachment_image( $client['client-logo'], 'full', '', array( 'alt' => $case_study_text ) );
			}
			elseif ( $client_logo && $case_study_link ) {
				// Build image and link
				$client_list .= "<a href='" . $case_study_link . "'>" . $client_logo . "</a>";
			}
			elseif ( $client_logo ) {
				$client_list .= $client_logo;
			}
			$client_list .= "</li>\n";
		}

		$client_list .= "</ul>\n";

		return $client_list;
	}
	else {
		return false;
	}
}

/**
 * Echos an unordered list of client logos or returns false
 * @param $page_id
 *
 * @return bool
 */

function sz_the_clients( $page_id ) {

	if ( sz_get_the_clients( $page_id ) ) {
		echo sz_get_the_clients( $page_id );
	}
	else {
		return false;
	}

}

function sz_get_the_testimonials( $page_id ){
	$testimonials = get_post_meta( $page_id, 'testimonial', false );
	if ( $testimonials ) {
		$testimonial_list = "\n<ul>\n";
		foreach ( $testimonials as $testimonial ) {
			$testimonial_image = false;
			$testimonial_content = false;
			$testimonial_list .= "\t<li class='row'>";
			if ( $testimonial['testimonial-image'] ) {
				$testimonial_image = wp_get_attachment_image( $testimonial['testimonial-image'], 'full', '', array( 'class' => 'columns small-3' ) );
			}
			if ( $testimonial['testimonial-content'] ) {
				$testimonial_content .= "<div class='columns small-9'>";
				$testimonial_content .=  wpautop( $testimonial['testimonial-content'] );
				$testimonial_content .= "</div>";
			}
			$testimonial_list .= $testimonial_image;
			$testimonial_list .= $testimonial_content;
			$testimonial_list .= "</li>\n";
		}

		$testimonial_list .= "</ul>\n";

		return $testimonial_list;
	}
	else {
		return false;
	}
}


function sz_the_testimonials( $page_id ) {

	if ( sz_get_the_testimonials( $page_id ) ) {
		echo sz_get_the_testimonials( $page_id );
	}
	else {
		return false;
	}

}