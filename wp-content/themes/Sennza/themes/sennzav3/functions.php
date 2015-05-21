<?php
/**
 * _s functions and definitions
 *
 * @package Sennza Version 3
 */

/**
 * Define constant for CMB
 */
if ( ! defined( 'CMB_URL' ) )
	define( 'CMB_URL', get_template_directory_uri() . '/inc/cmb' );

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 640; /* pixels */
}

if ( ! function_exists( 'sz_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function sz_setup() {

	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 * If you're building a theme based on _s, use a find and replace
	 * to change 'sennzaversion3' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'sennzaversion3', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Enable support for Post Thumbnails on posts and pages
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form' ) );

	set_post_thumbnail_size( 1140, 370 );
	add_image_size( 'featured-blogroll', 1140, 370, true );
	add_image_size( 'case-study-featured-image', 1920, 9999 );
	add_image_size( 'homepage-featured-image', 1920, 880, true );
	// Image sizes for Interchange
	add_image_size( 'responsive-retina', 1920, 9999 );
	add_image_size( 'responsive-large', 1024, 99999 );
	add_image_size( 'responsive-medium', 768, 99999 );
	add_image_size( 'responsive-small', 480, 99999 );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'sennzaversion3' ),
	) );

	/**
	 * Enable support for Post Formats
	 */
	add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );

	/**
	 * Setup the WordPress core custom background feature.
	 */
	add_theme_support( 'custom-background', apply_filters( 'sz_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	/**
	 * Add Editor Styles
	 */
	add_editor_style();
}
endif; // sz_setup
add_action( 'after_setup_theme', 'sz_setup' );

/**
 * Register widgetized area and update sidebar with default widgets
 */
function sz_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer Contact Area', 'sennzaversion3' ),
		'id'            => 'footer-contact',
		'description'   => __( 'This widget area controls the contact form in the footer.', 'sennzaversion3' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s column large-10 large-centered">',
		'after_widget'  => '</section>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'sz_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function sz_scripts() {
	wp_enqueue_style( 'sz-style', get_stylesheet_uri() );

	// Add Modernizr as Foundation requires it
	wp_enqueue_script( 'modernizr', get_template_directory_uri() . '/js/modernizr.js', array(), '2.6.2' );

	// Add fontawesome
	wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css' );

	// Add Foundation scripts
	wp_enqueue_script( 'foundation', get_template_directory_uri() . '/js/foundation.min.js', array( 'jquery' ), '5.0', true );
	wp_enqueue_script( 'interchange', get_template_directory_uri() . '/js/foundation.interchange.js', array( 'jquery', 'foundation' ), '5.0', true );

	// Add our main JavsScript file
	wp_enqueue_script( 'sz-main', get_template_directory_uri() . '/js/main.js', array( 'jquery' ), '1.0', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array(), '3.0' );
	wp_localize_script( 'sz-main', 'maps_vars', array(
			'template_dir_uri' => get_template_directory_uri(),
		) );

	wp_enqueue_style( 'sz-genericons', get_stylesheet_directory_uri() . '/css/genericons.css', '', '3.0.3', 'all' );

	// Load the Internet Explorer specific stylesheet.
	wp_enqueue_style( 'sz-ie', get_template_directory_uri() . '/css/ie.css', array( 'sz-style', 'sz-genericons' ), '20130207' );
	wp_style_add_data( 'sz-ie', 'conditional', 'lt IE 9' );

	wp_enqueue_script( 'ie-rem',  get_template_directory_uri() . '/js/rem.min.js', '', '1.0', true );

	wp_enqueue_script( 'fast-fonts', 'http://fast.fonts.net/jsapi/68a9d134-2c4c-42b6-813d-1811ecbc85eb.js', array(), false );
}
add_action( 'wp_enqueue_scripts', 'sz_scripts' );

/**
 * Add in excerpts for pages
 */
function sz_add_page_excerpts(){
	add_post_type_support( 'page', 'excerpt' );
}

add_action( 'init', 'sz_add_page_excerpts' );

/* Let's add the includes. Unused includes will be deleted during setup  */
foreach ( glob( get_template_directory() . '/inc/*.php' ) as $filename ) {
	require_once $filename;
}