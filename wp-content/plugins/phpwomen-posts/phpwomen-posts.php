<?php
/**
 * Plugin Name: BK PluginName
 * Version: 1.0
 * Plugin URI:
 * Description: Plugin Description
 * Text Domain: BK-pluginname
 */
namespace PHPWomen\Posts;

require plugin_dir_path(__FILE__) . '/vendor/autoload.php';

new Plugin(plugin_dir_path(__FILE__));