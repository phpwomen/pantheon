<?php
/**
 * The template for displaying a "No posts found" message.
 *
 * @since 1.0.6
 */
?>
	<article id="post-0" class="post error404 not-found">
		<?php if ( ! is_single() ) { ?>
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
		<?php } ?>
		   	   	<h1 class="entry-title"><?php _e( 'Nothing found', 'ward' ); ?></h1>

		        <div class="entry-content">
		            <p><?php _e( 'No results were found. Please try again.', 'ward' ); ?></p>
		        </div>
		<?php if ( ! is_single() ) { ?>
				</div>
			</div>
		</div>
		<?php } ?>
    </article><!-- #post-0.post -->