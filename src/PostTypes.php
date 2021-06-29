<?php

namespace My\Events;

class PostTypes
{
    public static function init()
    {
        add_action('init', [__CLASS__, 'registerPostTypes']);
    }

    public static function registerPostTypes()
    {
        register_post_type('event', [
            'labels'             => [
                'name'                  => _x('Events', 'Post type general name', 'my-events'),
                'singular_name'         => _x('Event', 'Post type singular name', 'my-events'),
                'menu_name'             => _x('Events', 'Admin Menu text', 'my-events'),
                'name_admin_bar'        => _x('Event', 'Add New on Toolbar', 'my-events'),
                'add_new'               => __('Add New', 'my-events'),
                'add_new_item'          => __('Add New Event', 'my-events'),
                'new_item'              => __('New Event', 'my-events'),
                'edit_item'             => __('Edit Event', 'my-events'),
                'view_item'             => __('View Event', 'my-events'),
                'all_items'             => __('Events', 'my-events'),
                'search_items'          => __('Search Events', 'my-events'),
                'parent_item_colon'     => __('Parent Events:', 'my-events'),
                'not_found'             => __('No events found.', 'my-events'),
                'not_found_in_trash'    => __('No events found in Trash.', 'my-events'),
                'featured_image'        => _x('Event Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-events'),
                'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'archives'              => _x('Event archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-events'),
                'insert_into_item'      => _x('Insert into event', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-events'),
                'uploaded_to_this_item' => _x('Uploaded to this event', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-events'),
                'filter_items_list'     => _x('Filter events list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-events'),
                'items_list_navigation' => _x('Events list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-events'),
                'items_list'            => _x('Events list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-events'),
            ],
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'my-events',
            'query_var'          => true,
            'rewrite'            => ['slug' => 'event'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title'],
        ]);

        register_post_type('invitee', [
            'labels'             => [
                'name'                  => _x('Invitees', 'Post type general name', 'my-invitees'),
                'singular_name'         => _x('Invitee', 'Post type singular name', 'my-invitees'),
                'menu_name'             => _x('Invitees', 'Admin Menu text', 'my-invitees'),
                'name_admin_bar'        => _x('Invitee', 'Add New on Toolbar', 'my-invitees'),
                'add_new'               => __('Add New', 'my-invitees'),
                'add_new_item'          => __('Add New Invitee', 'my-invitees'),
                'new_item'              => __('New Invitee', 'my-invitees'),
                'edit_item'             => __('Edit Invitee', 'my-invitees'),
                'view_item'             => __('View Invitee', 'my-invitees'),
                'all_items'             => __('Invitees', 'my-invitees'),
                'search_items'          => __('Search Invitees', 'my-invitees'),
                'parent_item_colon'     => __('Parent Invitees:', 'my-invitees'),
                'not_found'             => __('No invitees found.', 'my-invitees'),
                'not_found_in_trash'    => __('No invitees found in Trash.', 'my-invitees'),
                'featured_image'        => _x('Invitee Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-invitees'),
                'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-invitees'),
                'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-invitees'),
                'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-invitees'),
                'archives'              => _x('Invitee archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-invitees'),
                'insert_into_item'      => _x('Insert into invitee', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-invitees'),
                'uploaded_to_this_item' => _x('Uploaded to this invitee', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-invitees'),
                'filter_items_list'     => _x('Filter invitees list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-invitees'),
                'items_list_navigation' => _x('Invitees list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-invitees'),
                'items_list'            => _x('Invitees list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-invitees'),
            ],
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => current_user_can('administrator'),
            'show_in_menu'       => current_user_can('administrator') ? 'my-events' : false,
            'query_var'          => false,
            'rewrite'            => ['slug' => 'invitee'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title'],
            'capabilities'       => [
                'publish_posts'       => 'update_core',
                'edit_others_posts'   => 'update_core',
                'delete_posts'        => 'update_core',
                'delete_others_posts' => 'update_core',
                'read_private_posts'  => 'update_core',
                'edit_post'           => 'update_core',
                'delete_post'         => 'update_core',
                'read_post'           => 'update_core',
            ],
        ]);
    }
}
