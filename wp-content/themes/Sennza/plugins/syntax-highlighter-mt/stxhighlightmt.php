<?php
/*
    Copyright (C) 2014  Megatome Technologies

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/*
Plugin Name: Syntax Highlighter MT
Plugin URI: http://www.megatome.com/syntaxhighlighter
Description: Provides a simple way to use the Syntax Highlighter tool from <a href="http://alexgorbatchev.com/wiki/SyntaxHighlighter">http://alexgorbatchev.com/wiki/SyntaxHighlighter</a>
Version: 2.2.4
Author: Chad Johnston
Author URI: http://www.megatome.com
*/

$mt_pluginVersion = '2.2.4';

$themes = array(
    "Default" => "shThemeDefault.css",
    "Django" => "shThemeDjango.css",
    "Eclipse" => "shThemeEclipse.css",
    "Emacs" => "shThemeEmacs.css",
    "FadeToGrey" => "shThemeFadeToGrey.css",
    "MDUltra" => "shThemeMDUltra.css",
    "Midnight" => "shThemeMidnight.css",
    "RDark" => "shThemeRDark.css");

register_activation_hook(__FILE__, 'add_defaults_fn');
// Define default option settings
function add_defaults_fn() {
    $arr = array("theme"=>"Default");
    update_option('mtsh_plugin_options', $arr);
}

function mtsh_enqueue_scripts()
{
    $options = get_option('mtsh_plugin_options');
    global $themes;
    global $mt_pluginVersion;
    wp_enqueue_script( 'mt-shcore', plugins_url('scripts/shCore.js', __FILE__), array(), $mt_pluginVersion);
    wp_enqueue_script( 'mt-shautoloader', plugins_url('scripts/shAutoloader.js', __FILE__), array('mt-shcore'), $mt_pluginVersion);

    wp_enqueue_script( 'mt-brushtypes', plugins_url('brushTypes.js', __FILE__), array(), $mt_pluginVersion, true);
    wp_localize_script( 'mt-brushtypes', 'MTBrushParams', array('baseUrl' => plugins_url('', __FILE__)) );

    wp_enqueue_style( 'mt-shcore-style', plugins_url('styles/shCore.css', __FILE__), array(), $mt_pluginVersion);
    $selectedTheme = $themes['Default'];
    foreach ($themes as $k => $v) {
        if ($options['theme']== $k) {
            $selectedTheme = $v;
        }
    }
    wp_enqueue_style( 'mt-theme-style', plugins_url("styles/$selectedTheme", __FILE__), array('mt-shcore-style'), $mt_pluginVersion);
}

add_action( 'wp_enqueue_scripts', 'mtsh_enqueue_scripts' );

add_action('admin_menu', 'mtsh_plugin_settings_page');
function mtsh_plugin_settings_page()
{
    add_options_page('Syntax Highlighter MT', 'Syntax Highlighter MT', 'manage_options', 'mtsh_plugin', 'mtsh_plugin_options_page');
}

function mtsh_plugin_options_page()
{
    ?>
<div>
    <h2>SyntaxHighlighter MT Options</h2>
    <form action="options.php" method="post">
        <?php settings_fields('mtsh_plugin_options'); ?>
        <?php do_settings_sections(__FILE__); ?>
        <br/>
        <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>"/>
    </form>
    <br/>
    <hr/>
    For more information about this plugin, please see <a href="http://megatome.com/syntaxhighlighter">http://megatome.com/syntaxhighlighter</a><br/>
    For more information about the code this plugin is built upon, go to <a href="http://alexgorbatchev.com/wiki/SyntaxHighlighter">http://alexgorbatchev.com/wiki/SyntaxHighlighter</a>
</div>
<?php

}

add_action('admin_init', 'plugin_admin_init');
function plugin_admin_init()
{
    register_setting('mtsh_plugin_options', 'mtsh_plugin_options');
    add_settings_section('plugin_main', 'Highlighting Theme', 'mtsh_settings_theme', __FILE__);
    add_settings_field('theme', 'Theme', 'mtsh_settings_theme_dropdown', __FILE__, 'plugin_main');
}

function mtsh_settings_theme() {
    echo "<strong>Select the desired coloring theme for highlighting code. This will affect all highlighted code.</strong>";
}

function  mtsh_settings_theme_dropdown() {
	$options = get_option('mtsh_plugin_options');
	global $themes;
	echo "<select id='drop_down1' name='mtsh_plugin_options[theme]'>";
	foreach($themes as $k => $v) {
		$selected = ($options['theme']== $k) ? 'selected="selected"' : '';
		echo "<option value='$k' $selected>$k</option>";
	}
	echo "</select>";
}

?>