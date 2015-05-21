<?php
/**
 * This will delete all the slider custom post types when the plugin is uninstalled
 */

//If uninstall.php isn't called from WordPress then exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

//Remove all the custom post types
global $wpdb;
$wpdb->query(
	$wpdb->prepare(
	"DELETE FROM `wp_posts`
	WHERE `post_type` = %s",
	'slider')
);