<?php
/**
 * The first/left sidebar widgetized area.
 *
 * If no active widgets in sidebar, alert with default login
 * widget will appear.
 *
 * @since 1.0.0
 */
?>
<div id="secondary" <?php bavotasan_sidebar_class(); ?> role="complementary">
	<?php if ( ! dynamic_sidebar( 'sidebar' ) ) : ?>

	<?php if ( current_user_can( 'edit_theme_options' ) ) { ?>
		<div class="alert alert-warning"><?php printf( __( 'Add your own widgets by going to the %sWidgets admin page%s.', 'ward' ), '<a href="' . admin_url( 'widgets.php' ) . '">', '</a>' ); ?></div>
	<?php } ?>

	<aside id="meta" class="widget">
		<h3 class="widget-title"><?php _e( 'Meta', 'ward' ); ?></h3>
		<ul>
			<?php wp_register(); ?>
			<li><?php wp_loginout(); ?></li>
			<?php wp_meta(); ?>
		</ul>
	</aside>
	<?php endif; ?>
</div><!-- #secondary.widget-area -->