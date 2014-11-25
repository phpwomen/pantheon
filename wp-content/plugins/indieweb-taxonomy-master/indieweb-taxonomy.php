<?php
/**
 * Plugin Name: IndieWeb Custom Taxonomy
 * Plugin URI: https://github.com/dshanske/indieweb-taxonomy
 * Description: Adds a semantic layer to Posts, allowing them to be classified as likes, replies, favorites, etc.
 * Version: 0.11
 * Author: David Shanske
 * Author URI: http://david.shanske.com
 * License: CC0
 */

// Register Kind to Distinguish the Types of Posts

require_once( plugin_dir_path( __FILE__ ) . 'class.taxonomy-single-term.php');
require_once( plugin_dir_path( __FILE__ ) . 'walker.taxonomy-single-term.php');

require_once( plugin_dir_path( __FILE__ ) . '/iwt-config.php');
// Add Kind Post Metadata
require_once( plugin_dir_path( __FILE__ ) . '/kind-postmeta.php');
// Add Kind Functions
require_once( plugin_dir_path( __FILE__ ) . '/kind-functions.php');
// Add Kind Display Functions
require_once( plugin_dir_path( __FILE__ ) . '/kind-view.php');
// Add Embed Functions for Commonly Embedded Websites not Supported by Wordpress
require_once( plugin_dir_path( __FILE__ ) . '/embeds.php');

// Load Dashicons or Genericons in Front End in Order to Use Them in Response Display
// Load a local stylesheet
add_action( 'wp_enqueue_scripts', 'kindstyle_load' );
function kindstyle_load() {
//        wp_enqueue_style( 'dashicons' );
        // wp_enqueue_style( 'genericons', '//cdn.jsdelivr.net/genericons/3.1/genericons.css', array(), '3.1' );
	wp_enqueue_style( 'genericons', plugin_dir_url( __FILE__ ) . '/genericons/genericons.css', array(), null );
        wp_enqueue_style( 'kind-style', plugin_dir_url( __FILE__ ) . 'css/kind-style.css');
  }

function it_publish ( $ID, $post=null)
  {
     $response_url = get_post_meta($ID, 'response_url', true);
     if (!empty($response_url))
	 {
     		send_webmention(get_permalink($ID), $response_url);
 	 }
  }


add_filter('publish_post', 'it_publish', 10, 3);



add_action( 'init', 'register_taxonomy_kind' );

function register_taxonomy_kind() {

        $labels = array( 
        'name' => _x( 'Kinds', 'kind' ),
        'singular_name' => _x( 'Kind', 'kind' ),
        'search_items' => _x( 'Search Kinds', 'kind' ),
        'popular_items' => _x( 'Popular Kinds', 'kind' ),
        'all_items' => _x( 'All Kinds', 'kind' ),
        'parent_item' => _x( 'Parent Kind', 'kind' ),
        'parent_item_colon' => _x( 'Parent Kind:', 'kind' ),
        'edit_item' => _x( 'Edit Kind', 'kind' ),
        'update_item' => _x( 'Update Kind', 'kind' ),
        'add_new_item' => _x( 'Add New Kind', 'kind' ),
        'new_item_name' => _x( 'New Kind', 'kind' ),
        'separate_items_with_commas' => _x( 'Separate kinds with commas', 'kind' ),
        'add_or_remove_items' => _x( 'Add or remove kinds', 'kind' ),
        'choose_from_most_used' => _x( 'Choose from the most used kinds', 'kind' ),
        'menu_name' => _x( 'Kinds', 'kind' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => false,
        'show_in_nav_menus' => true,
        'show_ui' => false,
        'show_tagcloud' => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'kind', array('post'), $args );
}

// Sets up some starter terms...unless terms already exist 
// or any of the existing terms are defined
function kind_defaultterms () {

    // see if we already have populated any terms
    $kinds = get_terms( 'kind', array( 'hide_empty' => false ) );
     // if no terms then lets add our terms
    if( empty($kinds) ) {
	if (!term_exists('Like', 'kind')) {
	      wp_insert_term('Like', 'kind', 
		array(
   		 	  'description'=> 'Like',
    			  'slug' => 'like',
		     ) );

            }  
        if (!term_exists('Favorite', 'kind')) {
              wp_insert_term('Favorite', 'kind',
                array(
                          'description'=> 'Favorite',
                          'slug' => 'favorite',
                     ) );

            } 
        if (!term_exists('Reply', 'kind')) {
              wp_insert_term('Reply', 'kind',
                array(
                          'description'=> 'Reply',
                          'slug' => 'reply',
                     ) );

            }
        if (!term_exists('RSVP', 'kind')) {
              wp_insert_term('RSVP', 'kind',
                array(
                          'description'=> 'RSVP for Event',
                          'slug' => 'rsvp',
                     ) );

            }
        if (!term_exists('Repost', 'kind')) {
              wp_insert_term('Repost', 'kind',
                array(
                          'description'=> 'Repost',
                          'slug' => 'repost',
                     ) );

            }
        if (!term_exists('Bookmark', 'kind')) {
              wp_insert_term('Bookmark', 'kind',
                array(
                          'description'=> 'Sharing a Link',
                          'slug' => 'bookmark',
                     ) );

            }



 	}
}

add_action( 'init', 'kind_defaultterms'); 

add_filter('post_link', 'kind_permalink', 10, 3);
add_filter('post_type_link', 'kind_permalink', 10, 3);
 
function kind_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%kind%') === FALSE) return $permalink;
     
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;
 
        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'kind');   
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0])) $taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'standard';
 
    return str_replace('%kind%', $taxonomy_slug, $permalink);
}   

function json_rest_add_kindmeta($_post,$post,$context) {
	$response = get_post_meta( $post["ID"], 'response');
	if (!empty($response)) { $_post['response'] = $response; }
	return $_post;
}

add_filter("json_prepare_post",'json_rest_add_kindmeta',10,3);

?>
