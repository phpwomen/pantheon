<?php
/**
 * Set out default theme for this repo
 */

// Set a default theme
defined( 'WP_DEFAULT_THEME' ) or define( 'WP_DEFAULT_THEME', 'sennzav3' );
$table_prefix  = 'sen_';

// URL to the content directory
// You'll probably want to change this.
define( 'WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content' );