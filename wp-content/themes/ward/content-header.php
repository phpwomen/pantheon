<?php
/**
 * The template for displaying article headers
 *
 * @since 1.0.6
 */
$bavotasan_theme_options = bavotasan_theme_options();
$display_categories = $bavotasan_theme_options['display_categories'];

	if ( ! empty( $display_categories ) && 'page' != get_post_type() ) { ?>
	<h3 class="post-category"><?php the_category( ', ' ); ?></h3>
	<?php } ?>
	<h1 class="entry-title">
		<?php if ( is_single() ) : ?>
			<?php the_title(); ?>
		<?php else : ?>
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
		<?php endif; // is_single() ?>
	</h1>

	<div class="entry-meta">
		<?php
		$display_author = $bavotasan_theme_options['display_author'];
		if ( $display_author )
			printf( __( 'by %s', 'ward' ),
				'<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '" title="' . esc_attr( sprintf( __( 'Posts by %s', 'ward' ), get_the_author() ) ) . '" rel="author">' . get_the_author() . '</a>'
			);

		$display_date = $bavotasan_theme_options['display_date'];
		if( $display_date ) {
			if( $display_author )
				echo '&nbsp;' . __( 'on', 'ward' ) . '&nbsp;';

		    echo '<a href="' . get_permalink() . '" class="time"><time class="published updated" datetime="' . get_the_date( 'Y-m-d' ) . '">' . get_the_date() . '</time></a>';
	    }

		$display_comments = $bavotasan_theme_options['display_comment_count'];
		if( $display_comments && comments_open() ) {
			if ( $display_author || $display_date )
				echo '&nbsp;&bull;&nbsp;';

			comments_popup_link( __( '0 Comments', 'ward' ), __( '1 Comment', 'ward' ), __( '% Comments', 'ward' ) );
		}
		?>
	</div>