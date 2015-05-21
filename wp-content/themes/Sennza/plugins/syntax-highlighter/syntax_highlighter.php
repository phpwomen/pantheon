<?php
/*
Plugin Name: Syntax Highlighter for WordPress
Plugin URI: http://wppluginsj.sourceforge.jp/syntax-highlighter/
Description: 100% JavaScript syntax highlighter This plugin makes using the <a href="http://alexgorbatchev.com/SyntaxHighlighter">Syntax highlighter</a> to highlight code snippets within WordPress simple. Supports Bash, C++, C#, CSS, Delphi, Java, JavaScript, PHP, Python, Ruby, SQL, VB, VB.NET, XML, and (X)HTML.
Version: 3.0.83.3
Author: wokamoto
Author URI: http://dogmap.jp/
Text Domain: syntax-highlighter
Domain Path: /languages/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2008 - 2011 wokamoto (email : wokamoto1973@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Includes:
 SyntaxHighlighter Ver.3.0.83 (July 02 2010)
  http://alexgorbatchev.com/SyntaxHighlighter
  SyntaxHighlighter is donationware. If you are using it, please donate.
  http://alexgorbatchev.com/SyntaxHighlighter/donate.html
  Copyright (C) 2004-2010 Alex Gorbatchev.
  Dual licensed under the MIT and GPL licenses.

*/
if (!class_exists('wokController') || !class_exists('wokScriptManager'))
	require(dirname(__FILE__).'/includes/common-controller.php');

class SyntaxHighlighter extends wokController {	/* Start Class */
	var $plugin_ver = '3.0.83';
	var $version;

	var $sh_versions = array( '3.0.83', '2.1.364' );
	var $sh_themes   = array( 'Default', 'Django', 'Eclipse', 'Emacs', 'FadeToGrey', 'MDUltra', 'Midnight', 'RDark' );

	var $theme = 'Default';
	var $default_atts = array(
		 'num' => 1
		,'lang' => 'plain'
		,'lang_name' => 'false'
		,'highlight_lines' => ''
		,'collapse' => 'false'
		,'gutter' => 'true'
		,'ruler' => 'false'
		,'toolbar' => 'true'
		,'smart_tabs' => 'true'
		,'tab_size' => '4'
		,'light' => 'false'
		,'auto_link' => 'false'
		,'font_size' => '100%'
		,'encode' => 'false'
		);

	var $target = array(
		'AS3' ,
		'Bash' ,
		'ColdFusion' ,
		'CSharp' ,
		'Cpp' ,
		'JavaScript' ,
		'JavaFX' ,
		'JAVA' ,
		'Delphi' ,
		'Diff' ,
		'Erlang' ,
		'Groovy' ,
		'Patch' ,
		'Pascal' ,
		'Perl' ,
		'PHP' ,
		'Python' ,
		'Plain' ,
		'PowerShell' ,
		'Ruby' ,
		'Scala' ,
		'Shell' ,
		'Text' ,
		'vbnet' ,
		'VB' ,
		'SQL' ,
		'CSS' ,
		'XHTML' ,
		'XML' ,
		'XSLT' ,
		'HTML' ,
		'C' ,
		);
	var $options;
	var $languages;
	var $parsed = false;

	var $have_short_code = array(
		'checked' => false,
		'enabled' => false,
		);

	/*
	* Constructor
	*/
	function SyntaxHighlighter() {
		$this->__construct();
	}
	function __construct() {
		$this->init(__FILE__);

		$this->options = (array)$this->getOptions();
		$this->version = ( isset($this->options["version"]) ? $this->options["version"] : $this->sh_versions[0] );
		$this->theme = ( isset($this->options["theme"]) ? $this->options["theme"] : $this->sh_themes[0] );
		if (version_compare($this->version, "3.0", "<") && $this->theme === 'MDUltra')
			$this->theme = $this->sh_themes[0];

		$this->languages = array(
			"as3"  => array(false, 'AS3', 'js/shBrushAS3.js', 'shBrushAS3') ,
			"bash" => array(false, 'Bash', 'js/shBrushBash.js', 'shBrushBash') ,
			"c" => array(false, 'C', 'js/shBrushCpp.js', 'shBrushCpp') ,
			"cpp" => array(false, 'C++',  'js/shBrushCpp.js', 'shBrushCpp') ,
			"c-sharp" => array(false, 'C#', 'js/shBrushCSharp.js', 'shBrushCSharp') ,
			"coldfusion" => array(false, 'ColdFusion', 'js/shBrushColdFusion.js', 'shBrushColdFusion') ,
			"jscript" => array(false, 'Java Script', 'js/shBrushJScript.js', 'shBrushJScript') ,
			"java" => array(false, 'JAVA', 'js/shBrushJava.js', 'shBrushJava') ,
			"javafx" => array(false, 'JavaFX', 'js/shBrushJavaFX.js', 'shBrushJavaFX') ,
			"delphi" => array(false, 'Delphi', 'js/shBrushDelphi.js', 'shBrushDelphi') ,
			"diff" => array(false, 'Diff', 'js/shBrushDiff.js', 'shBrushDiff') ,
			"erlang" => array(false, 'Erlang', 'js/shBrushErlang.js', 'shBrushErlang') ,
			"groovy" => array(false, 'Groovy', 'js/shBrushGroovy.js', 'shBrushGroovy') ,
			"patch" => array(false, 'Patch', 'js/shBrushDiff.js', 'shBrushDiff') ,
			"pascal" => array(false, 'Pascal', 'js/shBrushDelphi.js', 'shBrushDelphi') ,
			"perl" => array(false, 'Perl', 'js/shBrushPerl.js', 'shBrushPerl') ,
			"php" => array(false, 'PHP', 'js/shBrushPhp.js', 'shBrushPhp') ,
			"plain" => array(false, 'Plain Text', 'js/shBrushPlain.js', 'shBrushPlain') ,
			"powershell" => array(false, 'PowerShell', 'js/shBrushPowerShell.js', 'shBrushPowerShell') ,
			"python" => array(false, 'Python', 'js/shBrushPython.js', 'shBrushPython') ,
			"ruby" => array(false, 'Ruby', 'js/shBrushRuby.js', 'shBrushRuby') ,
			"scala" => array(false, 'Scala', 'js/shBrushScala.js', 'shBrushScala') ,
			"shell" => array(false, 'Shell', 'js/shBrushBash.js', 'shBrushBash') ,
			"text" => array(false, 'Plain Text', 'js/shBrushPlain.js', 'shBrushPlain') ,
			"vb" => array(false, 'VB', 'js/shBrushVb.js', 'shBrushVb') ,
			"vb.net" => array(false, 'VB.Net', 'js/shBrushVb.js', 'shBrushVb') ,
			"sql" => array(false, 'SQL', 'js/shBrushSql.js', 'shBrushSql') ,
			"css" => array(false, 'CSS', 'js/shBrushCss.js', 'shBrushCss') ,
			"xml" => array(false, 'XML', 'js/shBrushXml.js', 'shBrushXml') ,
			"html" => array(false, 'HTML', 'js/shBrushXml.js', 'shBrushXml') ,
			"xhtml" => array(false, 'XHTML', 'js/shBrushXml.js', 'shBrushXml') ,
			"xslt" => array(false, 'XSLT', 'js/shBrushXml.js', 'shBrushXml') ,
			);

		if ( is_admin() ) {
			add_action('admin_menu', array(&$this, 'admin_menu'));
			add_filter('plugin_action_links', array(&$this, 'plugin_setting_links'), 10, 2 );

		} else {
			if ( $this->isKtai() ) {
				add_filter('the_content', array(&$this, 'parse_shortcodes'), 7);
			} else {
				add_action('wp_print_styles', array(&$this, 'add_stylesheet'));
				add_action('wp_head', array(&$this, 'add_head'));
				add_action('atom_head', array(&$this, 'add_feed_head'));
				add_action('rdf_header', array(&$this, 'add_feed_head'));
				add_action('rss2_head', array(&$this, 'add_feed_head'));
				add_action('rss_head', array(&$this, 'add_feed_head'));
			}
		}
	}

	//**************************************************************************************
	// Add Admin Menu
	//**************************************************************************************
	function admin_menu() {
		$this->addOptionPage(__('Syntax Highlighter', $this->textdomain_name), array($this, 'option_page'));
	}

	function plugin_setting_links($links, $file) {
		if (method_exists($this, 'addPluginSettingLinks')) {
			$links = $this->addPluginSettingLinks($links, $file);
		} else {
			$this_plugin = plugin_basename(__FILE__);
			if ($file == $this_plugin) {
				$settings_link = '<a href="' . $this->admin_action . '">' . __('Settings') . '</a>';
				array_unshift($links, $settings_link); // before other links
			}
		}
		return $links;
	}

	//**************************************************************************************
	// Show Option Page
	//**************************************************************************************
	function option_page() {
		if (isset($_POST['options_update'])) {
			if ($this->wp25) {
				check_admin_referer("update_options", "_wpnonce_update_options");
			}

			// Update options
			$this->options = $this->_options_update($this->stripArray($_POST));
			$this->note .= "<strong>".__('Done!', $this->textdomain_name)."</strong>";

		} elseif(isset($_POST['options_delete'])) {
			if ($this->wp25) {
				check_admin_referer("delete_options", "_wpnonce_delete_options");
			}

			// options delete
			$this->options = $this->_delete_settings();
			$this->note .= "<strong>".__('Done!', $this->textdomain_name)."</strong>";
			$this->error++;
		}

		$out  = '';

		// Add Options
		$out .= "<div class=\"wrap\">\n";
		$out .= "<form method=\"post\" id=\"update_options\" action=\"".$this->admin_action."\">\n";
		$out .= "<h2>".__('Syntax Highlighter Options', $this->textdomain_name)."</h2><br />\n";
		if ($this->wp25) {
			$out .= $this->makeNonceField("update_options", "_wpnonce_update_options", true, false);
		}

		$out .= '<p>';
		$out .= __('Version', $this->textdomain_name).':&nbsp;';
		$out .= '<select name="version" id="version">';
		foreach ($this->sh_versions as $version) {
			$out .= '<option value="' . $version . '"' . ($this->options['version']==$version ? ' selected="selected"' : '') . '>' . $version .'</option>';
		}
		$out .= '</select>';
		$out .= '&nbsp;&nbsp;';

		$out .= __('Theme', $this->textdomain_name).':&nbsp;';
		$out .= '<select name="theme" id="theme">';
		foreach ($this->sh_themes as $theme) {
			$out .= '<option value="' . $theme . '"' . ($this->options['theme']==$theme ? ' selected="selected"' : '') . '>' . $theme .'</option>';
		}
		$out .= '</select>';

		$out .= "</p>\n";

		// Add Update Button
		$out .= "<p style=\"margin-top:1em\"><input type=\"submit\" name=\"options_update\" class=\"button-primary\" value=\"".__('Update Options', $this->textdomain_name)." &raquo;\" class=\"button\" /></p>";
		$out .= "</form></div>\n";

		// Options Delete
		$out .= "<div class=\"wrap\" style=\"margin-top:2em;\">\n";
		$out .= "<h2>" . __('Uninstall', $this->textdomain_name) . "</h2><br />\n";
		$out .= "<form method=\"post\" id=\"delete_options\" action=\"".$this->admin_action."\">\n";
		if ($this->wp25) {
			$out .= $this->makeNonceField("delete_options", "_wpnonce_delete_options", true, false);
		}
		$out .= "<p>" . __('All the settings of &quot;Syntax Highlighter&quot; are deleted.', $this->textdomain_name) . "</p>";
		$out .= "<input type=\"submit\" name=\"options_delete\" class=\"button-primary\" value=\"".__('Delete Options', $this->textdomain_name)." &raquo;\" class=\"button\" />";
		$out .= "</form></div>\n";

		// Output
		echo (!empty($this->note) ? "<div id=\"message\" class=\"updated fade\"><p>{$this->note}</p></div>\n" : '') . "\n";
		echo ($this->error == 0 ? $out : '') . "\n";
	}

	//**************************************************************************************
	// Update Settings
	//**************************************************************************************
	function _options_update($recv_param) {
		$this->version = isset($recv_param['version']) ? $recv_param['version'] : $this->version;
		$this->theme   = isset($recv_param['theme'])   ? $recv_param['theme']   : $this->theme;
		$options = array(
			'version' => $this->version,
			'theme' => $this->theme,
			);

		// options update
		$this->options = $options;
		$this->updateOptions();

		return $options;
	}

	//**************************************************************************************
	// Delete Settings
	//**************************************************************************************
	private function _delete_settings() {
		$this->deleteOptions();

		$this->version = $this->sh_versions[0];
		$this->theme   = $this->sh_themes[0];
		$options = array(
			'version' => $this->version,
			'theme' => $this->theme,
			);

		return $options;
	}

	//**************************************************************************************
	// Add stylesheet
	//**************************************************************************************
	function add_stylesheet() {
		if ( function_exists('wp_enqueue_style') && $this->have_short_code() !== FALSE && !$this->isKtai() && !is_feed() ) {
			$sh_url = $this->plugin_url.$this->version.'/';
			wp_enqueue_style('shCore', "{$sh_url}css/shCore.css", array(), $this->version, 'all');
			if (version_compare($this->version, "3.0", ">=")) {
				wp_enqueue_style('shCore'.$this->theme, "{$sh_url}css/shCore{$this->theme}.css", array('shCore'), $this->version, 'all');
			}
			wp_enqueue_style('shTheme'.$this->theme, "{$sh_url}css/shTheme{$this->theme}.css", array('shCore'), $this->version, 'all');
		}
	}

	function add_head() {
		if ( $this->have_short_code() !== FALSE && !$this->isKtai() ) {
			if (!function_exists('wp_enqueue_style')) {
				$sh_url = $this->plugin_url.$this->version.'/';
				$out = "<link href=\"{$sh_url}css/shCore.css?ver={$this->version}\" type=\"text/css\" rel=\"stylesheet\" media=\"all\" />\n";
				if (version_compare($this->version, "3.0", ">=")) {
					$out .= "<link href=\"{$sh_url}css/shCore{$this->theme}.css?ver={$this->version}\" type=\"text/css\" rel=\"stylesheet\" media=\"all\" />\n";
				}
				$out .= "<link href=\"{$sh_url}css/shTheme{$this->theme}.css?ver={$this->version}\" type=\"text/css\" rel=\"stylesheet\" media=\"all\" />\n";
				echo $out;
			}

			add_filter('the_excerpt', array(&$this, 'parse_shortcodes'), 7);
			add_filter('the_content', array(&$this, 'parse_shortcodes'), 7);
			add_action('wp_footer', array(&$this, 'add_footer'));
		}
	}

	function add_feed_head() {
		if ( $this->have_short_code() !== FALSE ) {
			add_filter('the_excerpt_rss', array(&$this, 'parse_shortcodes'), 7);
			add_filter('the_content', array(&$this, 'parse_shortcodes'), 7);
		}
	}

	//**************************************************************************************
	// Add JavaScript
	//**************************************************************************************
	function add_footer(){
		$enabled = false;
		foreach ($this->languages as $key => $val) {
			if ($val[0]) {
				$enabled = true;
				break;
			}
		}
		if (!$enabled)
			return;

		$sh_url   = $this->plugin_url.$this->version.'/';
		$scripts  = "<script type=\"text/javascript\" src=\"{$sh_url}js/shCore.js?ver={$this->version}\"></script>\n";

		// AS3
		if (isset($this->languages["as3"]) && $this->languages["as3"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['as3'][2].'?ver='.$this->version.'"></script>'."\n";

		// Bash / shell
		if (isset($this->languages["bash"]) && $this->languages["bash"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['bash'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["shell"]) && $this->languages["shell"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['shell'][2].'?ver='.$this->version.'"></script>'."\n";

		// C / C++
		if (isset($this->languages["c"]) && $this->languages["c"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['c'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["cpp"]) && $this->languages["cpp"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['cpp'][2].'?ver='.$this->version.'"></script>'."\n";

		// C#
		if (isset($this->languages["c-sharp"]) && $this->languages["c-sharp"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['c-sharp'][2].'?ver='.$this->version.'"></script>'."\n";

		// ColdFusion
		if (isset($this->languages["coldfusion"]) && $this->languages["coldfusion"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['coldfusion'][2].'?ver='.$this->version.'"></script>'."\n";

		// Diff
		if (isset($this->languages["diff"]) && $this->languages["diff"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['diff'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["patch"]) && $this->languages["patch"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['patch'][2].'?ver='.$this->version.'"></script>'."\n";

		// Groovy
		if (isset($this->languages["groovy"]) && $this->languages["groovy"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['groovy'][2].'?ver='.$this->version.'"></script>'."\n";

		// Java
		if (isset($this->languages["java"]) && $this->languages["java"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['java'][2].'?ver='.$this->version.'"></script>'."\n";

		// JavaScript
		if (isset($this->languages["jscript"]) && $this->languages["jscript"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['jscript'][2].'?ver='.$this->version.'"></script>'."\n";

		// JavaFX
		if (isset($this->languages["javafx"]) && $this->languages["javafx"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['javafx'][2].'?ver='.$this->version.'"></script>'."\n";

		// Delphi
		if (isset($this->languages["delphi"]) && $this->languages["delphi"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['delphi'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["pascal"]) && $this->languages["pascal"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['pascal'][2].'?ver='.$this->version.'"></script>'."\n";

		// Erlang
		if (isset($this->languages["erlang"]) && $this->languages["erlang"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['erlang'][2].'?ver='.$this->version.'"></script>'."\n";

		// Perl
		if (isset($this->languages["perl"]) && $this->languages["perl"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['perl'][2].'?ver='.$this->version.'"></script>'."\n";

		// PHP
		if (isset($this->languages["php"]) && $this->languages["php"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['php'][2].'?ver='.$this->version.'"></script>'."\n";

		// Python
		if (isset($this->languages["python"]) && $this->languages["python"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['python'][2].'?ver='.$this->version.'"></script>'."\n";

		// Plain Text
		if (isset($this->languages["plain"]) && $this->languages["plain"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['plain'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["text"]) && $this->languages["text"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['text'][2].'?ver='.$this->version.'"></script>'."\n";

		// PowerShell
		if (isset($this->languages["powershell"]) && $this->languages["powershell"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['powershell'][2].'?ver='.$this->version.'"></script>'."\n";

		// Ruby
		if (isset($this->languages["ruby"]) && $this->languages["ruby"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['ruby'][2].'?ver='.$this->version.'"></script>'."\n";

		// Scala
		if (isset($this->languages["scala"]) && $this->languages["scala"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['scala'][2].'?ver='.$this->version.'"></script>'."\n";

		// SQL
		if (isset($this->languages["sql"]) && $this->languages["sql"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['sql'][2].'?ver='.$this->version.'"></script>'."\n";

		// Visual Basic
		if (isset($this->languages["vb"]) && $this->languages["vb"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['vb'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["vb.net"]) && $this->languages["vb.net"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['vb.net'][2].'?ver='.$this->version.'"></script>'."\n";

		// CSS
		if (isset($this->languages["css"]) && $this->languages["css"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['css'][2].'?ver='.$this->version.'"></script>'."\n";

		// XML / (X)HTML
		if (isset($this->languages["xml"]) && $this->languages["xml"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['xml'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["html"]) && $this->languages["html"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['html'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["xhtml"]) && $this->languages["xhtml"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['xhtml'][2].'?ver='.$this->version.'"></script>'."\n";
		elseif (isset($this->languages["xslt"]) && $this->languages["xslt"][0])
			$scripts .= '<script type="text/javascript" src="'.$sh_url.$this->languages['xslt'][2].'?ver='.$this->version.'"></script>'."\n";

		echo $scripts;

		$js_out  = '';
		if (version_compare($this->version, "2.0", "<")) {
			// -- for SyntaxHighlighter 1.5.x
			$js_out .= "dp.SyntaxHighlighter.Toolbar.Commands.About.label='" . __('?', $this->textdomain_name) . "';";
			$js_out .= "dp.SyntaxHighlighter.Toolbar.Commands.CopyToClipboard.label='" . __('copy to clipboard', $this->textdomain_name) . "';";
			$js_out .= "dp.SyntaxHighlighter.Toolbar.Commands.CopyToClipboard.func=function(B,A){var D=A.originalCode;var w=window,d=document;if(w.clipboardData){w.clipboardData.setData('text',D)}else{if(dp.sh.ClipboardSwf!=null){var C=A.flashCopier;if(C==null){C=d.createElement('div');A.flashCopier=C;A.div.appendChild(C)}C.innerHTML='<embed src=\"'+dp.sh.ClipboardSwf+'\" FlashVars=\"clipboard='+encodeURIComponent(D)+'\" width=\"0\" height=\"0\" type=\"application/x-shockwave-flash\"></embed>'}}alert(\"" . __('The code is in your clipboard now', $this->textdomain_name) . "\")};";
			$js_out .= "dp.SyntaxHighlighter.Toolbar.Commands.ExpandSource.label='" . __('+ expand source', $this->textdomain_name) . "';";
			$js_out .= "dp.SyntaxHighlighter.Toolbar.Commands.PrintSource.label='" . __('print', $this->textdomain_name) . "';";
			$js_out .= "dp.SyntaxHighlighter.Toolbar.Commands.ViewSource.label='" . __('view plain', $this->textdomain_name) . "';";
			$js_out .= "dp.SyntaxHighlighter.ClipboardSwf = '{$sh_url}js/clipboard.swf';\n";
			$js_out .= "dp.SyntaxHighlighter.HighlightAll('code');\n";
		} elseif (version_compare($this->version, "3.0", "<")) {
			// -- for SyntaxHighlighter 2.x
			$js_out .= 'SyntaxHighlighter.config.strings.expandSource="' . __('+ expand source', $this->textdomain_name) . '";';
			$js_out .= 'SyntaxHighlighter.config.strings.viewSource="' . __('view plain', $this->textdomain_name) . '";';
			$js_out .= 'SyntaxHighlighter.config.strings.copyToClipboard="' . __('copy to clipboard', $this->textdomain_name) . '";';
			$js_out .= 'SyntaxHighlighter.config.strings.copyToClipboardConfirmation="' . __('The code is in your clipboard now', $this->textdomain_name) . '";';
			$js_out .= 'SyntaxHighlighter.config.strings.print="' . __('print', $this->textdomain_name) . '";';
			$js_out .= 'SyntaxHighlighter.config.strings.help="' . __('?', $this->textdomain_name) . '";';
			$js_out .= 'SyntaxHighlighter.config.strings.noBrush="' . __("Can't find brush for: ", $this->textdomain_name) . '";';
			$js_out .= 'SyntaxHighlighter.config.strings.brushNotHtmlScript="' . __("Brush wasn't made for html-script option: ", $this->textdomain_name) . '";';
			$js_out .= "SyntaxHighlighter.config.clipboardSwf=\"{$sh_url}js/clipboard.swf\";\n";
			$js_out .= "SyntaxHighlighter.all();\n";
		} else {
			// -- for SyntaxHighlighter 3.x
			$js_out .= "SyntaxHighlighter.all();\n";
		}

		$this->writeScript($js_out, 'footer');
	}

	//**************************************************************************************
	// Add Shortcode
	//**************************************************************************************
	function shortcode_handler($atts, $content = null, $startTag) {
		extract(shortcode_atts($this->default_atts, $atts));

		if (strtolower($encode) === 'true')
			$encode = true;
		elseif ($content != strip_tags($content))
			$encode = true;
		else
			$encode = false;

		$lang_name = (strtolower($lang_name) == 'true');

		if (strtolower($startTag) === 'code')
			$startTag = strtolower($lang);
		$pVal = (int) $num;				// get the starting line number

		$outTxt = '';

		$inTxt = (
			$encode
			? htmlentities($content, ENT_QUOTES, get_settings('blog_charset'))
			: $content
			);
		if (isset($this->languages[$startTag])) {
			$this->languages[$startTag][0] = true;
		}

		if ($lang_name) {
			$outTxt = "\n\n"
				. '<p class="lang-name">'
				. $this->languages[$startTag][1]
				. '</p>'
				. "\n"
				;
		}

		$pre_tag = '<pre>';
		if (!$this->isKtai() && !is_feed()) {
			if (version_compare($this->version, "2.0", "<")) {
				// -- for SyntaxHighlighter 1.5.x
				$pre_tag = '<pre'
					. ' name="code"'
					. ' class="'.$startTag.($pVal > 1 ? ":firstLine[{$pVal}]" : '') . '"'
					. '>';
			} else {
				// -- for SyntaxHighlighter 2.x or 3.x
				$pre_tag = '<pre'
					. ' class="'
					. "brush: {$startTag};"
					. ($pVal > 1 ? " first-line: {$pVal};" : '')
					. (!empty($highlight_lines) ? " highlight: [{$highlight_lines}];" : '')
					. (strtolower($collapse) == 'true' ? ' collapse: true;' : '')
					. (strtolower($gutter) == 'false' ? ' gutter: false;' : '')
					. (strtolower($ruler) == 'true' ? ' ruler: true;' : '')
					. (strtolower($toolbar) == 'false' ? ' toolbar: false;' : '')
					. (strtolower($smart_tabs) == 'false' ? ' smart-tabs: false;' : '')
					. (strtolower($tab_size) != '4' ? ' tab-size: ' . (int)$tab_size . ';' : '')
					. (strtolower($auto_link) == 'false' ? ' auto-links: false;' : '')
					. (strtolower($light) == 'true' ? ' light: true;' : '')
					. ($font_size != '100%' ? " font-size: {$font_size};" : '')
					. '"'
					. '>';
			}
		}
		$outTxt .=  "{$pre_tag}{$inTxt}</pre>\n\n";

		return $outTxt;
	}

	function have_short_code() {
		if (is_admin())
			return FALSE;

		if ($this->have_short_code['checked'])
			return $this->have_short_code['enabled'];

		global $wp_query;

		$found = array();

		if (isset($wp_query->posts)) {
			$pattern = '/\[(code';
			foreach ($this->target as $val) {
				$pattern .= '|' . strtolower($val);
			}
			$pattern .= ')([\s]+[^\]]*\]|\])/im';
			$hasTeaser = !( is_single() || is_page() );
			foreach((array)$wp_query->posts as $key => $post) {
				$post_content = isset($post->post_content) ? $post->post_content : '';
				if ( $hasTeaser && preg_match('/<!--more(.*?)?-->/', $post_content, $matches) ) {
					$content = explode($matches[0], $post_content, 2);
					$post_content = $content[0];
				}

				if (!empty($post_content) && preg_match_all($pattern, $post_content, $matches, PREG_SET_ORDER)) {
					foreach ((array) $matches as $match) {
						$found[$match[1]] = true;
					}
					unset($match);
				}
				unset($matches);
			}
		}

		$this->have_short_code['checked'] = true;
		$this->have_short_code['enabled'] = (count($found) > 0 ? $found : FALSE);

		return $this->have_short_code['enabled'];
	}

	function shortcode_code($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'code');}

	function shortcode_c($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'c');}
	function shortcode_cpp($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'cpp');}
	function shortcode_csharp($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'c-sharp');}
	function shortcode_java($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'java');}
	function shortcode_javascript($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'jscript');}
	function shortcode_delphi($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'delphi');}
	function shortcode_pascal($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'pascal');}
	function shortcode_perl($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'perl');}
	function shortcode_php($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'php');}
	function shortcode_python($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'python');}
	function shortcode_ruby($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'ruby');}
	function shortcode_vb($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'vb');}
	function shortcode_vbnet($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'vb.net');}
	function shortcode_scala($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'scala');}
	function shortcode_sql($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'sql');}
	function shortcode_css($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'css');}
	function shortcode_xml($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'xml');}
	function shortcode_html($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'html');}
	function shortcode_xhtml($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'xhtml');}
	function shortcode_xslt($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'xslt');}

	function shortcode_bash($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'bash');}
	function shortcode_diff($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'diff');}
	function shortcode_groovy($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'groovy');}
	function shortcode_patch($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'patch');}
	function shortcode_plain($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'plain');}
	function shortcode_shell($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'shell');}
	function shortcode_text($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'plain');}

	function shortcode_as3($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'as3');}
	function shortcode_coldfusion($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'coldfusion');}
	function shortcode_javafx($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'javafx');}
	function shortcode_erlang($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'erlang');}
	function shortcode_powershell($atts, $content = null) {return $this->shortcode_handler($atts, $content, 'powershell');}

	//**************************************************************************************
	// parse shortcodes
	//**************************************************************************************
	function parse_shortcodes( $content ) {
		global $shortcode_tags;

		$shortcode_tags_org = $shortcode_tags;
		remove_all_shortcodes();

		$this->add_shortcodes();
		$content = do_shortcode( $content );

		$shortcode_tags = $shortcode_tags_org;

		$this->parsed = true;

		return $content;
	}

	//**************************************************************************************
	// Add shortcodes
	//**************************************************************************************
	function add_shortcodes() {
		add_shortcode('code', array(&$this, 'shortcode_code'));
		foreach ($this->target as $val) {
			add_shortcode($val, array(&$this, 'shortcode_' . strtolower($val)));
			if (strtolower($val) !== $val) {
				add_shortcode(strtolower($val), array(&$this, 'shortcode_' . strtolower($val)));
			}
			if (strtoupper($val) !== $val) {
				add_shortcode(strtoupper($val), array(&$this, 'shortcode_' . strtolower($val)));
			}
		}
	}
}

new SyntaxHighlighter();
?>