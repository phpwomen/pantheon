<?php
/*!
* WordPress Social Login
*
* http://miled.github.io/wordpress-social-login/ | https://github.com/miled/wordpress-social-login
*  (c) 2011-2014 Mohamed Mrassi and contributors | http://wordpress.org/plugins/wordpress-social-login/
*/

/** 
* The LOC in charge of displaying WSL Admin GUInterfaces
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// --------------------------------------------------------------------

/**
* Generate wsl admin pages 
*
* wp-admin/options-general.php?page=wordpress-social-login&.. 
*/
function wsl_admin_main()
{
	// HOOKABLE: 
	do_action( "wsl_admin_main_start" );

	if ( ! current_user_can('manage_options') )
	{
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}

	if( ! wsl_check_requirements() )
	{
		wsl_admin_ui_fail();

		exit;
	}

	GLOBAL $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS;
	GLOBAL $WORDPRESS_SOCIAL_LOGIN_COMPONENTS;
	GLOBAL $WORDPRESS_SOCIAL_LOGIN_PROVIDERS_CONFIG;
	GLOBAL $WORDPRESS_SOCIAL_LOGIN_VERSION;

	if( isset( $_REQUEST["enable"] ) && isset( $WORDPRESS_SOCIAL_LOGIN_COMPONENTS[ $_REQUEST["enable"] ] ) )
	{
		$component = $_REQUEST["enable"];

		$WORDPRESS_SOCIAL_LOGIN_COMPONENTS[ $component ][ "enabled" ] = true;

		update_option( "wsl_components_" . $component . "_enabled", 1 );

		wsl_register_components();
	}

	if( isset( $_REQUEST["disable"] ) && isset( $WORDPRESS_SOCIAL_LOGIN_COMPONENTS[ $_REQUEST["disable"] ] ) )
	{
		$component = $_REQUEST["disable"];

		$WORDPRESS_SOCIAL_LOGIN_COMPONENTS[ $component ][ "enabled" ] = false;

		update_option( "wsl_components_" . $component . "_enabled", 2 );

		wsl_register_components();
	}

	$wslp            = "networks";
	$wsldwp          = 0;
	$assets_base_url = WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL . '/assets/img/16x16/';

	if( isset( $_REQUEST["wslp"] ) )
	{
		$wslp = trim( strtolower( strip_tags( $_REQUEST["wslp"] ) ) );
	}

	wsl_admin_ui_header( $wslp );

	if( isset( $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS[$wslp] ) && $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS[$wslp]["enabled"] )
	{
		if( isset( $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS[$wslp]["action"] ) && $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS[$wslp]["action"] )
		{ 
			do_action( $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS[$wslp]["action"] );
		}
		else
		{
			include "components/$wslp/index.php"; 
		}
	}
	else
	{
		wsl_admin_ui_error();
	}

	wsl_admin_ui_footer();

	// HOOKABLE: 
	do_action( "wsl_admin_main_end" );
}

// --------------------------------------------------------------------

/**
* Render wsl admin pages header (label and tabs)
*/
function wsl_admin_ui_header( $wslp = null )
{
	// HOOKABLE: 
	do_action( "wsl_admin_ui_header_start" );

	GLOBAL $WORDPRESS_SOCIAL_LOGIN_VERSION;
	GLOBAL $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS;
	
?>
<a name="wsltop"></a>
<div class="wsl-container">

	<?php
		// nag
	
		if( in_array( $wslp, array( 'networks', 'login-widget' ) ) and ( isset( $_REQUEST['settings-updated'] ) or isset( $_REQUEST['enable'] ) ) )
		{
			$active_plugins = implode('', (array) get_option('active_plugins') ); 
			$cache_enabled  = 
				strpos( $active_plugins, "w3-total-cache"   ) !== false | 
				strpos( $active_plugins, "wp-super-cache"   ) !== false | 
				strpos( $active_plugins, "quick-cache"      ) !== false | 
				strpos( $active_plugins, "wp-fastest-cache" ) !== false | 
				strpos( $active_plugins, "wp-widget-cache"  ) !== false | 
				strpos( $active_plugins, "hyper-cache"      ) !== false;

			if( $cache_enabled )
			{
				?>
					<div class="fade updated" style="margin: 4px 0 20px;">
						<p>
							<?php _wsl_e("<b>Note:</b> WSL has detected that you are using a caching plugin. If the saved changes didn't take effect immediately then you might need to empty the cache", 'wordpress-social-login') ?>.
						</p>
					</div>
				<?php
			}
		}
		
		if( get_option( 'wsl_settings_development_mode_enabled' ) )
		{
			?>
				<div class="fade error wsl-error-dev-mode-on" style="margin: 4px 0 20px;">
					<p>
						<?php _wsl_e('<b>Warning:</b> You are now running WordPress Social Login with DEVELOPMENT MODE enabled. This mode is not intend for live websites as it might raise serious security risks', 'wordpress-social-login') ?>.
					</p>
					<p>
						<a class="button-secondary" href="options-general.php?page=wordpress-social-login&wslp=tools#dev-mode"><?php _wsl_e('Change this mode', 'wordpress-social-login') ?></a>
						<a class="button-secondary" href="http://miled.github.io/wordpress-social-login/troubleshooting-advanced.html" target="_blank"><?php _wsl_e('Read about the development mode', 'wordpress-social-login') ?></a>
					</p>
				</div>
			<?php
		}

		if( get_option( 'wsl_settings_debug_mode_enabled' ) )
		{
			?>
				<div class="fade updated wsl-error-debug-mode-on" style="margin: 4px 0 20px;">
					<p>
						<?php _wsl_e('<b>Note:</b> You are now running WordPress Social Login with DEBUG MODE enabled. This mode is not intend for live websites as it might add to loading time and store unnecessary data on your server', 'wordpress-social-login') ?>.
					</p>
					<p>
						<a class="button-secondary" href="options-general.php?page=wordpress-social-login&wslp=tools#debug-mode"><?php _wsl_e('Change this mode', 'wordpress-social-login') ?></a>
						<a class="button-secondary" href="options-general.php?page=wordpress-social-login&wslp=watchdog"><?php _wsl_e('View WSL logs', 'wordpress-social-login') ?></a>
						<a class="button-secondary" href="http://miled.github.io/wordpress-social-login/troubleshooting-advanced.html" target="_blank"><?php _wsl_e('Read about the debug mode', 'wordpress-social-login') ?></a>
					</p>
				</div>
			<?php
		}
	?>

	<div class="alignright">
		<a style="font-size: 0.9em; text-decoration: none;" target="_blank" href="http://miled.github.io/wordpress-social-login/documentation.html"><?php _wsl_e('Docs', 'wordpress-social-login') ?></a> - 
		<a style="font-size: 0.9em; text-decoration: none;" target="_blank" href="http://miled.github.io/wordpress-social-login/support.html"><?php _wsl_e('Support', 'wordpress-social-login') ?></a> - 
		<a style="font-size: 0.9em; text-decoration: none;" target="_blank" href="https://github.com/miled/wordpress-social-login"><?php _wsl_e('Github', 'wordpress-social-login') ?></a>
	</div>

	<h1 <?php if( is_rtl() ) echo 'style="margin: 20px 0;"'; ?>>
		<?php _wsl_e( 'WordPress Social Login', 'wordpress-social-login' ) ?>

		<small><?php echo $WORDPRESS_SOCIAL_LOGIN_VERSION ?></small>
	</h1>

	<h2 class="nav-tab-wrapper">
		&nbsp;
		<?php
			$css_pull_right = "";

			foreach( $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS as $name => $settings )
			{
				if( $settings["enabled"] && ( $settings["visible"] || $wslp == $name ) )
				{
					if( isset( $settings["pull-right"] ) && $settings["pull-right"] )
					{
						$css_pull_right = "float:right";

						if( is_rtl() )
						{
							$css_pull_right = "float:left";
						}
					}

					?><a class="nav-tab <?php if( $wslp == $name ) echo "nav-tab-active"; ?>" style="<?php echo $css_pull_right; ?>" href="options-general.php?page=wordpress-social-login&wslp=<?php echo $name ?>"><?php if( isset( $settings["ico"] ) ) echo '<img style="margin: 0px; padding: 0px; border: 0px none;width: 16px; height: 16px;" src="' . WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL . '/assets/img/' . $settings["ico"] . '" />'; else _wsl_e( $settings["label"], 'wordpress-social-login' ); ?></a><?php
				}
			}
		?>
	</h2>

	<div id="wsl_admin_tab_content">
<?php
	// HOOKABLE: 
	do_action( "wsl_admin_ui_header_end" );
}

// --------------------------------------------------------------------

/**
* Renders wsl admin pages footer
*/
function wsl_admin_ui_footer()
{
	// HOOKABLE: 
	do_action( "wsl_admin_ui_footer_start" );

	GLOBAL $WORDPRESS_SOCIAL_LOGIN_VERSION;
?>
	</div> <!-- ./wsl_admin_tab_content -->  
	
<div class="clear"></div>

<?php
	wsl_admin_help_us_localize_note();

	// HOOKABLE: 
	do_action( "wsl_admin_ui_footer_end" );

	if( get_option( 'wsl_settings_development_mode_enabled' ) )
	{ 
		wsl_display_dev_mode_debugging_area();
 	}
}

// --------------------------------------------------------------------

/**
* Renders wsl admin error page
*/
function wsl_admin_ui_error()
{
	// HOOKABLE: 
	do_action( "wsl_admin_ui_error_start" );
?> 
<div id="wsl_div_warn">
	<h3 style="margin:0px;"><?php _wsl_e('Oops! We ran into an issue.', 'wordpress-social-login') ?></h3> 

	<hr />

	<p>
		<?php _wsl_e('Unknown or Disabled <b>Component</b>! Check the list of enabled components or the typed URL', 'wordpress-social-login') ?> .
	</p>

	<p>
		<?php _wsl_e("If you believe you've found a problem with <b>WordPress Social Login</b>, be sure to let us know so we can fix it", 'wordpress-social-login') ?>.
	</p>

	<hr />

	<div>
		<a class="button-secondary" href="http://miled.github.io/wordpress-social-login/support.html" target="_blank"><?php _wsl_e( "Report as bug", 'wordpress-social-login' ) ?></a>
		<a class="button-primary" href="options-general.php?page=wordpress-social-login&wslp=components" style="float:<?php if( is_rtl() ) echo 'left'; else echo 'right'; ?>"><?php _wsl_e( "Check enabled components", 'wordpress-social-login' ) ?></a>
	</div> 
</div>  
<?php
	// HOOKABLE: 
	do_action( "wsl_admin_ui_error_end" );
}

// --------------------------------------------------------------------

/**
* Renders WSL #FAIL page
*/
function wsl_admin_ui_fail()
{
	// HOOKABLE: 
	do_action( "wsl_admin_ui_fail_start" );
?>
<div class="wsl-container">
		<div style="background: none repeat scroll 0 0 #fff;border: 1px solid #e5e5e5;box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);padding:20px;">
			<h1><?php _e("WordPress Social Login - FAIL!", 'wordpress-social-login') ?></h1>

			<hr />

			<p> 
				<?php _e('Despite the efforts, put into <b>WordPress Social Login</b> in terms of reliability, portability, and maintenance by the plugin <a href="http://profiles.wordpress.org/miled/" target="_blank">author</a> and <a href="https://github.com/hybridauth/WordPress-Social-Login/graphs/contributors" target="_blank">contributors</a>', 'wordpress-social-login') ?>. 
				<b style="color:red;"><?php _e('Your server failed the requirements check for this plugin', 'wordpress-social-login') ?>:</b>
			</p> 

			<p> 
				<?php _e('These requirements are usually met by default by most "modern" web hosting providers, however some complications may occur with <b>shared hosting</b> and, or <b>custom wordpress installations</b>', 'wordpress-social-login') ?>.
			</p> 

			<p>
				<?php _wsl_e("The minimum server requirements are", 'wordpress-social-login') ?>:
			</p>

			<ul style="margin-left:60px;">
				<li><?php _wsl_e("PHP >= 5.2.0 installed", 'wordpress-social-login') ?></li> 
				<li><?php _wsl_e("WSL Endpoint URLs reachable", 'wordpress-social-login') ?></li>
				<li><?php _wsl_e("PHP's default SESSION handling", 'wordpress-social-login') ?></li>
				<li><?php _wsl_e("PHP/CURL/SSL Extension enabled", 'wordpress-social-login') ?></li> 
				<li><?php _wsl_e("PHP/JSON Extension enabled", 'wordpress-social-login') ?></li> 
				<li><?php _wsl_e("PHP/REGISTER_GLOBALS Off", 'wordpress-social-login') ?></li> 
				<li><?php _wsl_e("jQuery installed on WordPress backoffice", 'wordpress-social-login') ?></li> 
			</ul>
		</div>

<?php
	include_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/admin/components/tools/wsl.components.tools.actions.job.php' );

	wsl_component_tools_do_diagnostics();
?>
</div>
<style>.wsl-container .button-secondary { display:none; }</style>
<?php
	// HOOKABLE: 
	do_action( "wsl_admin_ui_fail_end" );
}

// --------------------------------------------------------------------

/**
* Renders wsl admin welcome panel
*/
function wsl_admin_welcome_panel()
{
	if( isset( $_REQUEST["wsldwp"] ) && (int) $_REQUEST["wsldwp"] )
	{
		$wsldwp = (int) $_REQUEST["wsldwp"];

		update_option( "wsl_settings_welcome_panel_enabled", wsl_get_version() );

		return;
	}

	// if new user or wsl updated, then we display wsl welcome panel
	if( get_option( 'wsl_settings_welcome_panel_enabled' ) == wsl_get_version() )
	{ 
		return;
	}
	
	$wslp = "networks"; 

	if( isset( $_REQUEST["wslp"] ) )
	{
		$wslp = $_REQUEST["wslp"];
	}	
?> 
<!-- 
	if you want to know if a UI was made by developer, then here is a tip: he will always use tables

	//> wsl-w-panel is shamelessly borrowed and modified from wordpress welcome-panel
-->
<div id="wsl-w-panel">
	<a href="options-general.php?page=wordpress-social-login&wslp=<?php echo $wslp ?>&wsldwp=1" id="wsl-w-panel-dismiss" <?php if( is_rtl() ) echo 'style="left: 10px;right: auto;"'; ?>><?php _wsl_e("Dismiss", 'wordpress-social-login') ?></a>
	
	<table width="100%" border="0" style="margin:0;padding:0;">
		<tr>
			<td width="10" valign="top"></td>
			<td width="300" valign="top">
				<b style="font-size: 16px;"><?php _wsl_e("Welcome!", 'wordpress-social-login') ?></b>
				<p>
					<?php _wsl_e("If you are still new to WordPress Social Login, we have provided a few walkthroughs to get you started", 'wordpress-social-login') ?>.
				</p>
			</td>
			<td width="40" valign="top"></td>
			<td width="260" valign="top">
				<br />
				<p>
					<b><?php _wsl_e("Get Started", 'wordpress-social-login') ?></b>
				</p>
				<ul style="margin-left:25px;">
					<li><a href="http://miled.github.io/wordpress-social-login/overview.html" target="_blank"><?php _wsl_e('Plugin Overview', 'wordpress-social-login') ?></a></li>
					<li><a href="http://miled.github.io/wordpress-social-login/networks.html" target="_blank"><?php _wsl_e('Setup and Configuration', 'wordpress-social-login') ?></a></li>
					<li><a href="http://miled.github.io/wordpress-social-login/widget.html" target="_blank"><?php _wsl_e('Customize WSL Widgets', 'wordpress-social-login') ?></a></li>
					<li><a href="http://miled.github.io/wordpress-social-login/userdata.html" target="_blank"><?php _wsl_e('Manage users and contacts', 'wordpress-social-login') ?></a></li> 
					<li><a href="http://miled.github.io/wordpress-social-login/documentation.html" target="_blank"><?php _wsl_e('WSL Developer API', 'wordpress-social-login') ?></a></li> 
				</ul>
			</td>
			<td width="" valign="top">
				<br />
				<p>
					<b><?php echo sprintf( _wsl__( "What's new on WSL %s", 'wordpress-social-login'), wsl_get_version() ) ?></b>
				</p>

				<ul style="margin-left:25px;">
					<li><?php _wsl_e('WSL now support authentications through <a href="https://dribbble.com" target="_blank">Dribbble.com</a>. Hooray for fellow designers', 'wordpress-social-login') ?>!</li> 
					<li><?php _wsl_e('<a href="http://store.steampowered.com/" target="_blank">Steam</a> provider has been entirely reworked and now fully support the new Web API', 'wordpress-social-login') ?>.</li> 
					<li><?php _wsl_e('WSL admin interfaces have been reworked and can be now extended with hooks', 'wordpress-social-login') ?>.</li> 
					<li><?php _wsl_e('Profile completion form has received a visual update', 'wordpress-social-login') ?>.</li>
					<li><?php _wsl_e('Bouncer Membership level can be now set to any user role', 'wordpress-social-login') ?>.</li>
					<li><?php _wsl_e('WSL now provide an <a href="http://miled.github.io/wordpress-social-login/developer-api-apis.html" target="_blank">easier access</a> to social networks apis', 'wordpress-social-login') ?>.</li>
					<li><?php _wsl_e('Fix several stability issues', 'wordpress-social-login') ?>.</li>
				</ul>
			</td>
		</tr>
		<tr id="wsl-w-panel-updates-tr">
			<td colspan="5" style="border-top:1px solid #ccc;" id="wsl-w-panel-updates-td">
				&nbsp;
			</td>
		</tr>
	</table> 
</div>
<?php 
}

// --------------------------------------------------------------------

/**
* Renders wsl localization note
*/
function wsl_admin_help_us_localize_note()
{
	return; // nothing, until I decide otherwise.. 

	$assets_url = WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL . '/assets/img/'; 

	?> 
		<div id="l10n-footer">
			<br /><br />
			<img src="<?php echo $assets_url ?>flags.png">
			<a href="https://www.transifex.com/projects/p/wordpress-social-login/" target="_blank"><?php _wsl_e( "Help us translate WordPress Social Login into your language", 'wordpress-social-login' ) ?></a>
		</div>
	<?php
}

// --------------------------------------------------------------------

/**
* Renders an editor in a page in the typical fashion used in Posts and Pages.
* wp_editor was implemented in wp 3.3. if not found we fallback to a regular textarea
*
* Utility.
*/
function wsl_render_wp_editor( $name, $content )
{
	if( ! function_exists( 'wp_editor' ) )
	{
		?>
			<textarea style="width:100%;height:100px;margin-top:6px;" name="<?php echo $name ?>"><?php echo htmlentities( $content ); ?></textarea> 
		<?php
		return;
	}
?>
<div class="postbox"> 
	<div class="wp-editor-textarea" style="background-color: #FFFFFF;">
		<?php 
			wp_editor( 
				$content, $name, 
				array( 'textarea_name' => $name, 'media_buttons' => true, 'tinymce' => array( 'theme_advanced_buttons1' => 'formatselect,forecolor,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink' ) ) 
			);
		?>
	</div> 
</div>
<?php
}

// --------------------------------------------------------------------

/**
* Display WordPress Social Login on settings as submenu 
*/
function wsl_admin_menu()
{
	add_options_page('WP Social Login', 'WP Social Login', 'manage_options', 'wordpress-social-login', 'wsl_admin_main' );

	add_action( 'admin_init', 'wsl_register_setting' );
}

add_action('admin_menu', 'wsl_admin_menu' ); 

// --------------------------------------------------------------------

/**
* Enqueue WSL admin CSS file
*/
function wsl_add_admin_stylesheets()
{
	if( ! wp_style_is( 'wsl-admin', 'registered' ) )
	{
		wp_register_style( "wsl-admin", WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL . "/assets/css/admin.css" ); 
	}

	wp_enqueue_style( "wsl-admin" ); 
}

add_action( 'admin_enqueue_scripts', 'wsl_add_admin_stylesheets' );

// --------------------------------------------------------------------
