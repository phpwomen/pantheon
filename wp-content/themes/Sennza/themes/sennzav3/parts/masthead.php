<?php
/**
 * The template for the Masthead on the Front Page
 */
?>
<div class="sennza-intro">
	<?php
	if ( has_post_thumbnail() ):
		the_post_thumbnail( 'homepage-featured-image', array( 'class' => 'intro-image' ) );
	endif;
	?>
	<div class="sennza-intro-wrapper">
		<div class="sennza-intro-content">
			<h1><?php the_title(); ?></h1>

			<div class="show-for-medium-up">
			<?php the_excerpt(); ?>
			</div>

			<?php sz_the_cta( get_the_ID() ); ?>
		</div>
	</div>

</div>
<?php
/**
 * Output the case studies in our Slider
 */
if ( class_exists( 'Pico_Slider' ) ):
	$slider = new Pico_Slider();
	$slider->do_slider();
endif;
?>
<div class="row clients">
	<div class="column large-10 large-centered">
	<h5><span>Some of our clients</span></h5>

	<?php sz_the_clients( get_the_ID() ); ?>
	</div>
</div>

<div class="testimonials">
	<div class="row">
		<div class="column large-10 large-centered">
			<h6><span>Why our clients love us!</span></h6>

		<?php sz_the_testimonials( get_the_ID() ); ?>
		</div>
	</div>
</div>