<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 * and the left sidebar conditional
 *
 * @since 1.0.0
 */
?><!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" <?php language_attributes(); ?>><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<?php wp_head(); ?>
</head>

<body <?php body_class( 'basic' ); ?>>

	<div id="page">

		<header class="navbar-inverse navbar navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			          <span class="icon-bar"></span>
			          <span class="icon-bar"></span>
			          <span class="icon-bar"></span>
			        </button>
					<a id="site-title" class="navbar-brand" href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?> <small><?php bloginfo( 'description' ); ?></small></a>
				</div>
				<h3 class="screen-reader-text"><?php _e( 'Main menu', 'ward' ); ?></h3>
				<a class="screen-reader-text" href="#primary" title="<?php esc_attr_e( 'Skip to content', 'ward' ); ?>"><?php _e( 'Skip to content', 'ward' ); ?></a>
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => 'nav', 'container_class' => 'navbar-collapse collapse', 'menu_class' => 'nav navbar-nav', 'fallback_cb' => 'bavotasan_default_menu' ) ); ?>
			</div>
		</header>

		<?php bavotasan_jumbotron(); ?>

		<?php if ( is_singular() && ! is_front_page() ) { ?>
		<div id="main" class="container">
			<div class="row">
		<?php } else { ?>
		<div id="main">
		<?php } ?>