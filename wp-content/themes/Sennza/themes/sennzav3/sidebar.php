<?php
/**
 * The Widget Area for the "Footer Contact" content
 *
 * @package Sennza Version 3
 */
?>
	<?php do_action( 'before_sidebar' ); ?>

	<?php if ( is_front_page() ): ?>
	<div id="interchangemap" data-interchange="[<?php echo get_template_directory_uri(); ?>/parts/google-map-image.php, (small)], [<?php echo get_template_directory_uri(); ?>/parts/google-map.php, (medium)], [<?php echo get_template_directory_uri(); ?>/parts/google-map.php, (large)]">
		<noscript><img class="static-map" src='<?php echo get_template_directory_uri();?>/images/map.png' /></noscript>
	</div>
	<?php endif; ?>
<?php if ( ! is_active_sidebar( 'footer-contact' ) ) {
	return;
}
?>

<div class="row" role="complementary">
	<?php dynamic_sidebar( 'footer-contact' ); ?>
</div><!-- #footer-sidebar -->
