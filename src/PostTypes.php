<?php

namespace My\Events;

class PostTypes
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('init', [__CLASS__, 'registerPostTypes']);
    }

    /**
     * Register post types
     */
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
            'rewrite'            => ['event'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title', 'thumbnail', 'comments'],
            'taxonomies'         => ['event_tag'],
        ]);

        register_post_type('invitee', [
            'labels'             => [
                'name'                  => _x('Invitees', 'Post type general name', 'my-events'),
                'singular_name'         => _x('Invitee', 'Post type singular name', 'my-events'),
                'menu_name'             => _x('Invitees', 'Admin Menu text', 'my-events'),
                'name_admin_bar'        => _x('Invitee', 'Add New on Toolbar', 'my-events'),
                'add_new'               => __('Add New', 'my-events'),
                'add_new_item'          => __('Add New Invitee', 'my-events'),
                'new_item'              => __('New Invitee', 'my-events'),
                'edit_item'             => __('Edit Invitee', 'my-events'),
                'view_item'             => __('View Invitee', 'my-events'),
                'all_items'             => __('Invitees', 'my-events'),
                'search_items'          => __('Search Invitees', 'my-events'),
                'parent_item_colon'     => __('Parent Invitees:', 'my-events'),
                'not_found'             => __('No invitees found.', 'my-events'),
                'not_found_in_trash'    => __('No invitees found in Trash.', 'my-events'),
                'featured_image'        => _x('Invitee Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-events'),
                'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'archives'              => _x('Invitee archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-events'),
                'insert_into_item'      => _x('Insert into invitee', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-events'),
                'uploaded_to_this_item' => _x('Uploaded to this invitee', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-events'),
                'filter_items_list'     => _x('Filter invitees list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-events'),
                'items_list_navigation' => _x('Invitees list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-events'),
                'items_list'            => _x('Invitees list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-events'),
            ],
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => current_user_can('administrator') ? 'my-events' : false,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title'],
        ]);

        register_post_type('invitee_group', [
            'labels'             => [
                'name'                  => _x('Invitee groups', 'Post type general name', 'my-events'),
                'singular_name'         => _x('Invitee group', 'Post type singular name', 'my-events'),
                'menu_name'             => _x('Invitee groups', 'Admin Menu text', 'my-events'),
                'name_admin_bar'        => _x('Invitee group', 'Add New on Toolbar', 'my-events'),
                'add_new'               => __('Add New', 'my-events'),
                'add_new_item'          => __('Add New Group', 'my-events'),
                'new_item'              => __('New Group', 'my-events'),
                'edit_item'             => __('Edit Group', 'my-events'),
                'view_item'             => __('View Group', 'my-events'),
                'all_items'             => __('Invitee groups', 'my-events'),
                'search_items'          => __('Search Groups', 'my-events'),
                'parent_item_colon'     => __('Parent Groups:', 'my-events'),
                'not_found'             => __('No groups found.', 'my-events'),
                'not_found_in_trash'    => __('No groups found in Trash.', 'my-events'),
                'featured_image'        => _x('Group Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-events'),
                'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'archives'              => _x('Group archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-events'),
                'insert_into_item'      => _x('Insert into group', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-events'),
                'uploaded_to_this_item' => _x('Uploaded to this group', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-events'),
                'filter_items_list'     => _x('Filter groups list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-events'),
                'items_list_navigation' => _x('Groups list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-events'),
                'items_list'            => _x('Groups list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-events'),
            ],
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'my-events',
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title'],
        ]);

        register_post_type('event_location', [
            'labels'             => [
                'name'                  => _x('Locations', 'Post type general name', 'my-events'),
                'singular_name'         => _x('Location', 'Post type singular name', 'my-events'),
                'menu_name'             => _x('Locations', 'Admin Menu text', 'my-events'),
                'name_admin_bar'        => _x('Location', 'Add New on Toolbar', 'my-events'),
                'add_new'               => __('Add New', 'my-events'),
                'add_new_item'          => __('Add New Location', 'my-events'),
                'new_item'              => __('New Location', 'my-events'),
                'edit_item'             => __('Edit Location', 'my-events'),
                'view_item'             => __('View Location', 'my-events'),
                'all_items'             => __('Locations', 'my-events'),
                'search_items'          => __('Search Locations', 'my-events'),
                'parent_item_colon'     => __('Parent Locations:', 'my-events'),
                'not_found'             => __('No locations found.', 'my-events'),
                'not_found_in_trash'    => __('No locations found in Trash.', 'my-events'),
                'featured_image'        => _x('Location Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-events'),
                'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'archives'              => _x('Location archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-events'),
                'insert_into_item'      => _x('Insert into location', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-events'),
                'uploaded_to_this_item' => _x('Uploaded to this location', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-events'),
                'filter_items_list'     => _x('Filter locations list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-events'),
                'items_list_navigation' => _x('Locations list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-events'),
                'items_list'            => _x('Locations list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-events'),
            ],
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'my-events',
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title'],
        ]);
    }
}
