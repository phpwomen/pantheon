<?php
/**
 * Plugin Name:  WordPress-GitHub Sync Custom Filters
 * Plugin URI:   https://github.com/benbalter/wordpress-github-sync
 * Description:  Adds support for custom post types and statuses
 * Version:      1.0.0
 * Author:       James DiGioia
 * Author URI:   https://jamesdigioia.com/
 * License:      GPL2
 */

add_filter('wpghs_whitelisted_post_types', function ($supported_post_types) {
  return array_merge($supported_post_types, array(
    // add your custom post types here
    'phpw_usergroups'
  ));
});

add_filter('wpghs_whitelisted_post_statuses', function ($supported_post_statuses) {
  return array_merge($supported_post_statuses, array(
    // additional statuses available: https://codex.wordpress.org/Post_Status
    'draft'
  ));
});
