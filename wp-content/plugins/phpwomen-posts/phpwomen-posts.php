<?php
/**
 * Plugin Name: PHPWomen Post Types
 * Version: 1.0
 * Plugin URI:
 * Description: Plugin Description
 * Text Domain: phpwomen_posts
 */
namespace PHPWomen\Posts;

require plugin_dir_path(__FILE__) . '/vendor/autoload.php';

new Plugin(plugin_dir_path(__FILE__));