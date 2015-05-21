<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Sennza Version 3
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/respond.min.js" type="text/javascript"></script>
<![endif]-->
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<?php do_action( 'before' ); ?>
	<header id="masthead" class="site-header" role="banner">
		<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'sennzaversion3' ); ?></a>
		<nav id="site-navigation" class="main-navigation top-bar" role="navigation" data-topbar>
			<ul class="title-area">
			<li class="name">
				<h1>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<div class="sz-site-logo">
						</div>
					</a>
				</h1>
			</li>
			<li class="toggle-topbar menu-icon"><a href="#">Menu<span></span></a></li>
			</ul>
			<section class="top-bar-section">
			<?php wp_nav_menu(
				array(
					'theme_location'  => 'primary',
					'container'       => false,
					'menu_class' => 'right'
				)
			); ?>
			</section>
		</nav><!-- #site-navigation -->
	</header><!-- #masthead -->

	<?php if ( is_front_page() ): ?>
		<?php get_template_part( 'parts/masthead' ); ?>
	<?php endif; ?>

	<div id="content" class="site-content">
