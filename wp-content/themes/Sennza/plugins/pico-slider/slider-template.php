<?php
$args = array(
	'post_type'      => 'slider',
	'posts_per_page' => 4
);
$slider_items = new WP_Query( $args );
if ( $slider_items ): ?>
	<div class="pico-slider-container">
		<div class="flexslider">
			<ul class="slides">
				<?php while ( $slider_items->have_posts() ) : $slider_items->the_post(); ?>
					<?php
					$image_alignment  = get_post_meta( $slider_items->post->ID, 'imagealignment', true );
					$slider_video_url = get_post_meta( $slider_items->post->ID, 'slider_video_url', true );
					$button_1_link    = get_post_meta( $slider_items->post->ID, 'button_1_link', true );
					$button_1_title   = get_post_meta( $slider_items->post->ID, 'button_1_title', true );
					$button_2_link    = get_post_meta( $slider_items->post->ID, 'button_2_link', true );
					$button_2_title   = get_post_meta( $slider_items->post->ID, 'button_2_title', true ); ?>
					<li class="row">
						<div class="slider-content-wrapper columns slider medium-6">
							<div class="slider-content">
								<?php the_content(); ?>
							</div>
						</div>
						<div class="slider-image columns medium-6">
							<?php if ( has_post_thumbnail() ): ?>
								<?php $size = 'post-thumbnail' ?>
								<?php $size = apply_filters( 'post_thumbnail_size', $size ); ?>
								<?php $thumbnail_args = array(
									'class' => "attachment-$size $image_alignment"
								); ?>
								<?php the_post_thumbnail( 'slider-thumb', $thumbnail_args ); ?>
							<?php else: ?>
								<?php echo wp_oembed_get( $slider_video_url, '' ); ?>
							<?php endif; ?>
						</div>
					</li>
				<?php endwhile; ?>
			</ul>
		</div>
	</div>
<?php else: ?>
	<p>There aren't any sliders.</p>
<?php endif; ?>
<?php wp_reset_query(); ?>