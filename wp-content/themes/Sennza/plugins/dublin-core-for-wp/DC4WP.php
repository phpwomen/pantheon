<?php
/*
Plugin Name: Dublin Core for WordPress
Plugin URI: http://wordpress.org/extend/plugins/dublin-core-for-wp/
Description: Inserts Dublin Core metadata (title, author, creation date, etc.) as meta tags for posts and pages. Small options screen in admin area.
Version: 0.7.2
Author: Tim McCormack, Salvatore Vassallo, Joan Junyent, Alex Oberhauser
Compatible: 2.9.2
Tags: Dublin Core, metadata

	Dublin Core Meta Tags released under the GNU General Public
	License (GPL) http://www.gnu.org/licenses/gpl.txt

	This is a WordPress plugin (http://www.wordpress.org/).
*/

/*==================*
 * Setup, if needed *
 *==================*/

/**
 * Ensure record in wp_options
 */
function dc4wp_add_options()
{
	add_option('dc4wp_rights', '', "The rights you are publishing your content under. (GPL, Copyright, CC-BY-SA, etc.)");
	add_option('dc4wp_license', '', "A URL pointing to the license terms for your content.");
}

//...and actually call it
dc4wp_add_options();


/*========================*
 * Generate HTML metadata *
 *========================*/

/**
 * Write a meta tag if the content is non-empty.
 */
function dc4wp_writeMetaTag($name, $content, $scheme = "")
{
	if($scheme)
	{
		$scheme = "scheme=\"{$scheme}\" ";
	}
	if($content)
	{
		echo "\n\t\t<meta name=\"{$name}\" content=\"{$content}\" {$scheme}/>";
	}
}

/**
 * Gather and output the Dublin Core metadata.
 */
function dc4wp_write_html_metadata()
{
	global $wp_query, $post, $wpdb;

	/* It only makes sense to put metadata in single articles. */
	if((is_single() || is_page()) && have_posts())
	{
		$wp_query->the_post();//load the $post variable
		
		$post_cats = get_the_category();
		$def_cat = get_option('default_category');//we want to ignore this one
		$DC_subjects = array();
		foreach($post_cats as $one_cat)
		{
			if($one_cat->cat_ID != $def_cat)//prevent "Uncategorized" from appearing
			{
				$DC_subjects[] = $one_cat->cat_name;
			}
		}
		$post_tags = get_the_tags();
		if($post_tags) { // sometimes array(), sometimes false for pages...
			foreach($post_tags as $one_tag) {
				$DC_subjects[] = $one_tag->name;
			}
		}
		
		$DC_title = the_title('', '', false);
		
		$auth_first = get_the_author_meta('first_name');
		$auth_last = get_the_author_meta('last_name');
		$auth_nick = get_the_author_meta('display_name');
		if(empty($auth_first) or empty($auth_last)) {
			$DC_creator_name = $auth_nick;
		} else {
			$DC_creator_name = "$auth_last, $auth_first";
		}

		$DC_identifier = get_permalink();
		$DC_date_created = get_the_time('Y-m-d\TH:i:s'); /* http://www.w3.org/TR/NOTE-datetime */

		$DC_publisher = get_settings('blogname');
		$DC_publisher_url = get_settings('home');

		$DC_language = get_bloginfo('language');	
			
//		$DC_description = get_bloginfo('description');
//		<meta name="DC.description" content="{$DC_description}" />
		
		$DC_license = get_option('dc4wp_license');

		$wp_query->rewind_posts();//reset so the loop doesn't freak out later on

echo <<<EOHTML
		<meta name="DC.publisher" content="{$DC_publisher}" />
		<meta name="DC.publisher.url" content="{$DC_publisher_url}/" />
		<meta name="DC.title" content="{$DC_title}" />
		<meta name="DC.identifier" content="{$DC_identifier}" />
		<meta name="DC.date.created" scheme="WTN8601" content="{$DC_date_created}" />
		<meta name="DC.created" scheme="WTN8601" content="{$DC_date_created}" />
		<meta name="DC.date" scheme="WTN8601" content="{$DC_date_created}" />
		<meta name="DC.creator.name" content="{$DC_creator_name}" />
		<meta name="DC.creator" content="{$DC_creator_name}" />
		<meta name="DC.rights.rightsHolder" content="{$auth_nick}" />		
EOHTML;
		dc4wp_writeMetaTag('DC.language', $DC_language, 'rfc1766');
		foreach($DC_subjects as $oneSub) {
			dc4wp_writeMetaTag('DC.subject', $oneSub);
		}
		dc4wp_writeMetaTag('DC.rights.license', $DC_license);
		dc4wp_writeMetaTag('DC.license', $DC_license);
		echo "\n";//make source more readable
	}
}

//Hook to print metadata
add_action('wp_head', 'dc4wp_write_html_metadata');


/*==========================*
 * Administration interface *
 *==========================*/

/**
 * Display options page.
 */
function dc4wp_options_page()
{
	global $wpdb;
	$updated = false;
	// Start updating
	if(isset($_POST['Submit']))
	{
		// updating license (dc.rights.license)
		$DC_license = $_POST['dc4wp_license'];
		update_option('dc4wp_license', $DC_license);
		$updated = true;
	} 		 
	$DC_license = get_option('dc4wp_license');
	if($updated)
	{
		echo <<<EOHTML
			<div class="updated">
				<p>Options saved.</p>
			</div>
EOHTML;
	}
	echo <<<EOHTML
		<div class="wrap">
			<h2>Dublin Core for Wordpress Settings</h2>
			<form method="post" action="{$_SERVER['REQUEST_URI']}">
				<fieldset class="options">      	
					<legend>Add metadata dublincore</legend>      	
					
					<label for="">License:</label>
					<input type="text" size="45" name="dc4wp_license" value="{$DC_license}" title="The URL of the license of publish under." />
					
					<p>Examples:</p>
					<ul>
						<li><a href="http://creativecommons.org/licenses/by-sa/2.5/" target="_blank">http://creativecommons.org/licenses/by-sa/2.5/</a></li>
						<li><a href="http://www.gnu.org/licenses/gpl.html" target="_blank">http://www.gnu.org/licenses/gpl.html</a></li>
						<li>Empty for no license metadata</li>
				</fieldset>
				<input type="submit" name="Submit" value="Update Options" />
			</form>
		</div>
EOHTML;
}

/**
 * Register the options page.
 */
function dc4wp_admin_menu()
{
	//title of page, name of option in menu bar, which function lays out the html
	add_options_page(__('DC4WP Options'), __('DC4WP'), 5, basename(__FILE__), 'dc4wp_options_page');
}

//Hook for option apge registration
add_action('admin_menu', 'dc4wp_admin_menu');

?>
