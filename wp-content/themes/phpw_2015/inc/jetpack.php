<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package phpw_2015
 */

/**
 * Add theme support for Infinite Scroll.
 * See: http://jetpack.me/support/infinite-scroll/
 */
function phpw_2015_jetpack_setup() {
	add_theme_support( 'infinite-scroll', array(
		'container' => 'main',
		'footer'    => 'page',
	) );
}
add_action( 'after_setup_theme', 'phpw_2015_jetpack_setup' );
