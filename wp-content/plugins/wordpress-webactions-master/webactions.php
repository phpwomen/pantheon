<?php
/*
 Plugin Name: web actions
 Plugin URI: https://github.com/pfefferle/wordpress-webactions
 Description: Adds [web action](http://indiewebcamp.com/webactions) support to some WordPress core features
 Author: pfefferle
 Author URI: http://notizblog.org/
 Version: 1.0.0-dev
*/

if (!class_exists('WebActionPlugin')) :

// initialize plugin
add_action('init', array( 'WebActionPlugin', 'init' ));

/**
 * WebAction Plugin Class
 *
 * @author Matthias Pfefferle
 */
class WebActionPlugin {

  /**
   * initialize the plugin, registering WordPress hooks.
   */
  public static function init() {
    add_filter('comment_reply_link', array('WebActionPlugin', 'comment_reply_link'), null, 4);
    add_action('comment_form_before', array('WebActionPlugin', 'comment_form_before'), 0);
    add_action('comment_form_after', array('WebActionPlugin', 'after'), 0);
  }

  /**
   * add webaction to the reply links in the comment section
   *
   * @param string $link the html representation of the comment link
   * @param array $args associative array of options
   * @param int $comment ID of comment being replied to
   * @param int $post ID of post that comment is going to be displayed on
   */
  public static function comment_reply_link( $link, $args, $comment, $post ) {
    $permalink = get_permalink($post->ID);

    return "<indie-action do='reply' with='".esc_url( add_query_arg( 'replytocom', $comment->comment_ID, $permalink ) )."'>$link</indie-action>";
  }

  /**
   * surround comment form with a reply action
   */
  public static function comment_form_before() {
    $post = get_queried_object();
    $permalink = get_permalink($post->ID);

    echo "<indie-action do='reply' with='$permalink'>";
  }

  /**
   * generic webaction "closer"
   */
  public static function after() {
    echo "</indie-action>";
  }
}

endif;