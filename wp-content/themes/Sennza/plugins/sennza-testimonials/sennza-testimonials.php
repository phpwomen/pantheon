<?php
/*
Plugin Name: Sennza Testimonials
Plugin URI: http://wwww.sennza.com.au/
Description: This plugin generates a custom post type for Testimonials
Author: Bronson Quick
Version: 1.0
Author URI: http://www.sennza.com.au/
 */

add_action( 'init', 'register_cpt_testimonial' );

function register_cpt_testimonial() {
    $args = array(
        'public' => true,
        'query_var' => 'testimonial',
        'rewrite' => array(
            'slug' => 'testimonials',
            'with_front' => false
        ),
        'supports' => array(
            'title',
            'editor',
            'author',
            'thumbnail',
            'revisions'
        ),
        'labels' => array(
            'name' => 'Testimonials',
            'singular_name' => 'Testimonial',
            'add_new' => 'Add New Testimonial',
            'add_new_item' => 'Add New Testimonial',
            'edit_item' => 'Edit Testimonial',
            'new_item' => 'New Testimonial',
            'view_item' => 'View Testimonial',
            'search_items' => 'Search Testimonials',
            'not_found' => 'No testimonials found',
            'not_found_in_trash' => 'No testimonials found in Trash',
        ),
    );
    register_post_type( 'testimonial', $args );
}