<?php

namespace PHPWomen\Posts\Lib;


class PostType
{
    public function __construct()
    {
        add_action('init', array($this, 'createPostTypes'));
    }

    public function createPostTypes()
    {
        $this->createUserGroupPostType();
        $this->createEventPostType();
    }

    public function createUserGroupPostType()
    {
        $labels = array(
            'name' => _x('UserGroups', 'Post Type General Name', 'phpwomen_posts_usergroup'),
            'singular_name' => _x('UserGroup', 'Post Type Singular Name', 'phpwomen_posts_usergroup'),
            'menu_name' => __('UserGroups', 'phpwomen_posts_usergroup'),
            'name_admin_bar' => __('UserGroups', 'phpwomen_posts_usergroup'),
            'parent_item_colon' => __('Parent Item:', 'phpwomen_posts_usergroup'),
            'all_items' => __('All UserGroups', 'phpwomen_posts_usergroup'),
            'add_new_item' => __('Add New UserGroup', 'phpwomen_posts_usergroup'),
            'add_new' => __('Add New', 'phpwomen_posts_usergroup'),
            'new_item' => __('New UserGroup', 'phpwomen_posts_usergroup'),
            'edit_item' => __('Edit UserGroup', 'phpwomen_posts_usergroup'),
            'update_item' => __('Update UserGroup', 'phpwomen_posts_usergroup'),
            'view_item' => __('View UserGroup', 'phpwomen_posts_usergroup'),
            'search_items' => __('Search UserGroup', 'phpwomen_posts_usergroup'),
            'not_found' => __('Not found', 'phpwomen_posts_usergroup'),
            'not_found_in_trash' => __('Not found in Trash', 'phpwomen_posts_usergroup'),
        );
        $rewrite = array(
            'slug' => 'post_type',
            'with_front' => true,
            'pages' => true,
            'feeds' => true,
        );
        $args = array(
            'label' => __('phpw_usergroup', 'phpwomen_posts_usergroup'),
            'description' => __('Post Type Description', 'phpwomen_posts_usergroup'),
            'labels' => $labels,
            'supports' => array('title', 'editor',),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-universal-access-alt',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'rewrite' => $rewrite,
            'capability_type' => 'page',
        );
        register_post_type('phpw_usergroup', $args);

        if (function_exists('acf_add_local_field_group')):

            if (function_exists('acf_add_local_field_group')):

                acf_add_local_field_group(array(
                    'key' => 'group_5558e5cd37bd5',
                    'title' => 'UserGroup Settings',
                    'fields' => array(
                        array(
                            'key' => 'field_5558e5e700f72',
                            'label' => 'UserGroup Logo',
                            'name' => 'usergroup_logo',
                            'type' => 'image',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'return_format' => 'url',
                            'preview_size' => 'thumbnail',
                            'library' => 'uploadedTo',
                            'min_width' => '',
                            'min_height' => '',
                            'min_size' => '',
                            'max_width' => '',
                            'max_height' => '',
                            'max_size' => '',
                            'mime_types' => '',
                        ),
                        array(
                            'key' => 'field_5558e66200f74',
                            'label' => 'UserGroup Website',
                            'name' => 'usergroup_website',
                            'type' => 'url',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                        ),
                        array(
                            'key' => 'field_5558e64500f73',
                            'label' => 'UserGroup Location',
                            'name' => 'usergroup_location',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                            'readonly' => 0,
                            'disabled' => 0,
                        ),
                        array(
                            'key' => 'field_5558e69800f75',
                            'label' => 'UserGroup Meeting Schedule',
                            'name' => 'usergroup_schedule',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                            'readonly' => 0,
                            'disabled' => 0,
                        ),
                    ),
                    'location' => array(
                        array(
                            array(
                                'param' => 'post_type',
                                'operator' => '==',
                                'value' => 'phpw_usergroup',
                            ),
                        ),
                    ),
                    'menu_order' => 0,
                    'position' => 'normal',
                    'style' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen' => '',
                ));

            endif;

        endif;
    }

    public function createEventPostType()
    {

    }
}

