<?php
//**************************************************************************************
// Require wp-load.php or wp-config.php
//**************************************************************************************
if(!function_exists('get_option')) {
	$path = (
		defined('ABSPATH')
		? ABSPATH
		: dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/'
		);
	require_once(
		file_exists($path.'wp-load.php')
		? $path.'wp-load.php'
		: $path.'wp-config.php'
		);
}

//**************************************************************************************
// Common Controller
//**************************************************************************************
if (!class_exists('wokController')) :
class wokController {
	var $file_path;
	var $plugins_dir, $plugin_dir, $plugin_file, $plugin_url;
	var $textdomain_name;
	var $options, $option_name;
	var $admin_option, $admin_action, $admin_hook;
	var $note, $error;
	var $charset;
	var $wp25, $wp26, $wp27, $wp28, $wp29, $wp30, $wp31;
	var $inline_js;

	var $jquery_js  = 'includes/js/jquery-1.4.2.min.js';
	var $jquery_ver = '1.4.2';

	var $jquery_noconflict_js = 'includes/js/jquery.noconflict.js';

	/*
	* initialize
	*/
	function init($file) {
		global $wp_version, $wok_script_manager;

		$this->charset = get_option('blog_charset');
		$this->wp25    = version_compare($wp_version, "2.5", ">=");
		$this->wp26    = version_compare($wp_version, "2.6", ">=");
		$this->wp27    = version_compare($wp_version, "2.7", ">=");
		$this->wp28    = version_compare($wp_version, "2.8", ">=");
		$this->wp29    = version_compare($wp_version, "2.9", ">=");
		$this->wp30    = version_compare($wp_version, "3.0", ">=");
		$this->wp31    = version_compare($wp_version, "3.1", ">=");

		$this->setPluginDir($file);
		$this->loadTextdomain();

		$this->note = '';
		$this->error = 0;

		$this->admin_option    = $this->plugin_file;
		$this->admin_action    =
			  trailingslashit(get_bloginfo('wpurl')) . 'wp-admin/'
			. ($this->wp27 ? 'options-general.php' : 'admin.php')
			. '?page=' . $this->admin_option;
		$this->admin_hook      = array();

		$this->options         = array();
		$this->option_name     = ( isset($this->plugin_name) && !empty($this->plugin_name)
					? $this->plugin_name
					: $this->plugin_dir )
					. " Options";

		if (!isset($wok_script_manager) && class_exists('wokScriptManager'))
			$wok_script_manager = new wokScriptManager();

		$this->inline_js        = array(
			'admin_head' => '',
			'head' => '',
			'footer' => '',
			);
	}

	function setPluginDir($file) {
		$this->file_path = $file;
		$this->plugins_dir = trailingslashit(defined('PLUGINDIR') ? PLUGINDIR : 'wp-content/plugins');
		$filename = explode("/", $this->file_path);
		if(count($filename) <= 1) $filename = explode("\\", $this->file_path);
		$this->plugin_dir  = $filename[count($filename) - 2];
		$this->plugin_file = $filename[count($filename) - 1];
		$this->plugin_url  = $this->wp_plugin_url($this->plugin_dir);
		unset($filename);
	}

	function loadTextdomain( $sub_dir = '' ) {
		$this->textdomain_name = $this->plugin_dir;
		$abs_plugin_dir = $this->wp_plugin_dir($this->plugin_dir);
		$sub_dir = (!empty($sub_dir)
			? preg_replace('/^\//', '', $sub_dir)
			: (file_exists($abs_plugin_dir.'languages') ? 'languages' : (file_exists($abs_plugin_dir.'language') ? 'language' : (file_exists($abs_plugin_dir.'lang') ? 'lang' : '')))
			);
		$textdomain_dir = trailingslashit(trailingslashit($this->plugin_dir) . $sub_dir);

		if ($this->wp26 && defined('WP_PLUGIN_DIR'))
			load_plugin_textdomain($this->textdomain_name, false, $textdomain_dir);
		else
			load_plugin_textdomain($this->textdomain_name, $this->plugins_dir . $textdomain_dir);
	}

	// Handles Add/strips slashes to the given array
	function stripArray($array) {
		if(!is_array($array))
			return stripslashes($array);

		foreach($array as $key => $value) {
			if (!is_array($value))
				$slashed_array[$key] = stripslashes($value);
			else
				$slashed_array[$key] = $this->stripArray($value);
		}
		return $slashed_array;
	}

	// Get Option
	function getOption($key) {
		if ($key=='version')
			return (isset($this->plugin_ver) ? $this->plugin_ver : false);
		else
			return (isset($this->options[$key]) ? $this->options[$key] : false);
	}

	// Get Text
	function getText($text) {
		return __($text, $this->textdomain_name);
	}

	// Get Options
	function getOptions(){
		return get_option($this->option_name);
	}

	// Update Options
	function updateOptions() {
		update_option($this->option_name, $this->options);
	}

	// Delete Options
	function deleteOptions() {
		delete_option($this->option_name);
	}

	// Add Admin Option Page
	function addOptionPage($page_title, $function, $capability = 'administrator', $menu_title = '', $file = '') {
		if ($menu_title == '')
			$menu_title = $page_title;
		if ($file == '')
			$file = $this->plugin_file;
		$this->admin_hook['option'] = add_options_page($page_title, $menu_title, $capability, $file, $function);
	}

	function addManagementPage($page_title, $function, $capability = 'administrator', $menu_title = '', $file = '') {
		if ($menu_title == '')
			$menu_title = $page_title;
		if ($file == '')
			$file = $this->plugin_file;
		$this->admin_hook['management'] = add_management_page($page_title, $menu_title, $capability, $file, $function);
	}

	function addThemePage($page_title, $function, $capability = 'administrator', $menu_title = '', $file = '') {
		if ($menu_title == '')
			$menu_title = $page_title;
		if ($file == '')
			$file = $this->plugin_file;
		$this->admin_hook['theme'] = add_theme_page($page_title, $menu_title, $capability, $file, $function);
	}

	function addSubmenuPage($parent, $page_title, $function, $capability = 'administrator', $menu_title = '', $file = '') {
		if ($menu_title == '')
			$menu_title = $page_title;
		if ($file == '')
			$file = $this->plugin_file;
		$this->admin_hook[$parent] = add_submenu_page($parent, $page_title, $menu_title, $capability, $file, $function);
	}

	function addMediaPage($page_title, $function, $capability = 'administrator', $menu_title = '', $file = '') {
		$this->addSubmenuPage(($this->wp27 ? 'upload.php' : 'edit.php'), $page_title, $function, $capability, $menu_title, $file);
	}

	function addEditPage($page_title, $function, $capability = 'administrator', $menu_title = '', $file = '') {
		$this->addSubmenuPage('edit.php', $page_title, $function, $capability, $menu_title, $file);
	}

	function addPluginSettingLinks($links, $file) {
		$this_plugin = plugin_basename($this->file_path);
		if ($file == $this_plugin) {
			$settings_link = '<a href="' . $this->admin_action . '">' . __('Settings') . '</a>';
			array_unshift($links, $settings_link); // before other links
		}
		return $links;
	}

	// Make Nonce field
	function makeNonceField($action = -1, $name = "_wpnonce", $referer = true , $echo = true ) {
		if ( !function_exists('wp_nonce_field') )
			return;
		else
			return wp_nonce_field($action, $name, $referer, $echo);
	}

	// This Plugin active?
	function isActive($file = '') {
		$is_active = false;
		if ($file == '')
			$file = $this->plugin_file;
		foreach ((array) get_option('active_plugins') as $val) {
			if (preg_match('/'.preg_quote($file).'/i', $val)) {
				$is_active = true;
				break;
			}
		}
		return $is_active;
	}

	// Mobile Access ?
	function isKtai(){
		return	(
			(function_exists('is_mobile') && is_mobile()) ||
			(function_exists('is_ktai')   && is_ktai())
			);
	}

	// Output Javascript
	function scriptConcat($js, $place) {
		return $js . $this->inline_js[$place];
	}
	function writeScript($out = '', $place = 'head') {
		global $wok_script_manager;
		if ($out == '' || !isset($wok_script_manager))
			return;
		$this->inline_js[$place] .= $out;
		add_filter($place.'_script/manageScripts', array(&$this, "scriptConcat"), 10, 2);
	}

	// Regist jQuery
	function addjQuery() {
		global $wok_script_manager;
		if (!isset($wok_script_manager))
			return false;

		if (function_exists('wp_register_script')) {
			$wok_script_manager->registerScript('jquery', $this->plugin_url.$this->jquery_js, false, $this->jquery_ver);
			wp_enqueue_script('jquery');
			add_filter('print_scripts_array', array($this, 'jQueryNoConflict'), 11);
			return true;
		} else {
			return false;
		}
	}

	// jQuery noConflict
	function jQueryNoConflict($args) {
		if (function_exists('wp_register_script')) {
			global $wok_script_manager;
			if (!isset($wok_script_manager))
				return false;

			$jquerypos = array_search('jquery', $args);
			if(false !== $jquerypos && in_array('prototype', $args)) {
				// Need to add a no conflict call after the jquery.
				wp_register_script('jquery.noconflict', $this->plugin_url.$this->jquery_noconflict_js ,array('jquery'));
				array_splice( $args, $jquerypos+1, 0, 'jquery.noconflict' );
			}
		}
		return $args;
	}

	// WP_CONTENT_DIR
	function wp_content_dir($path = '') {
		return trailingslashit( trailingslashit( defined('WP_CONTENT_DIR')
			? WP_CONTENT_DIR
			: trailingslashit(ABSPATH) . 'wp-content'
			) . preg_replace('/^\//', '', $path) );
	}

	// WP_CONTENT_URL
	function wp_content_url($path = '') {
		return trailingslashit( trailingslashit( defined('WP_CONTENT_URL')
			? WP_CONTENT_URL
			: trailingslashit(get_option('siteurl')) . 'wp-content'
			) . preg_replace('/^\//', '', $path) );
	}

	// WP_PLUGIN_DIR
	function wp_plugin_dir($path = '') {
		return trailingslashit($this->wp_content_dir( 'plugins/' . preg_replace('/^\//', '', $path) ));
	}

	// WP_PLUGIN_URL
	function wp_plugin_url($path = '') {
		return trailingslashit($this->wp_content_url( 'plugins/' . preg_replace('/^\//', '', $path) ));
	}

}
endif;

//**************************************************************************************
// JavaScript Manager
//**************************************************************************************
if (function_exists('wp_register_script') && !class_exists('wokScriptManager')) :
class wokScriptManager {
	/*
	* Constructor
	*/
	function wokScriptManager() {
		$this->__construct();
	}
	function __construct() {
//		global $wp_scripts;
//		if (!is_a($wp_scripts, 'WP_Scripts'))
//			$wp_scripts = new WP_Scripts();

		add_action('admin_head', array($this, 'adminHeadPrintScript'), 11);
		add_action('wp_head', array($this, 'headPrintScript'), 11);
		add_action('wp_footer', array($this, 'footerPrintScript'), 11);
	}

	function registerScript( $handle, $src = '', $deps = false, $ver = false ) {
		global $wp_scripts, $wp_version;

		if (version_compare($wp_version, "2.6", ">=")) {
			if (isset($wp_scripts->registered[$handle])) {
				if (version_compare($wp_scripts->registered[$handle]->ver, $ver, '<')) {
					if ($src  != '')
						$wp_scripts->registered[$handle]->src  = $src;
					if (is_array($deps))
						$wp_scripts->registered[$handle]->deps = $deps;
					if ($ver  != false)
						$wp_scripts->registered[$handle]->ver  = $ver;
				}
			} else {
				wp_register_script($handle, $src, $deps, $ver);
			}
		} else {
			if (isset($wp_scripts->scripts[$handle])) {
				if (version_compare($wp_scripts->scripts[$handle]->ver, $ver, '<')) {
					if ($src  != '')
						$wp_scripts->scripts[$handle]->src  = $src;
					if (is_array($deps))
						$wp_scripts->scripts[$handle]->deps = $deps;
					if ($ver  != false)
						$wp_scripts->scripts[$handle]->ver  = $ver;
				}
			} else {
				wp_register_script($handle, $src, $deps, $ver);
			}
		}
	}

	function dequeueScript( $handle ) {
		global $wp_scripts;
		$wp_scripts->dequeue( $handle );
	}

	function printScript($js) {
		if ($js != '') {
			echo "<script type=\"text/javascript\">//<![CDATA[\n";
			echo $js;
			echo "//]]></script>\n";
		}
	}

	function adminHeadPrintScript() {
		$this->printScript(apply_filters('admin_head_script/manageScripts', '', 'admin_head'));
	}

	function headPrintScript() {
		$this->printScript(apply_filters('head_script/manageScripts', '', 'head'));
	}

	function footerPrintScript() {
		$this->printScript(apply_filters('footer_script/manageScripts', '', 'footer'));
	}
}
endif;
