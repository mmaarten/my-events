<?php

namespace My\Events;

class PostTypes
{
    /**
     * Initialize
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
                'add_new_item'          => __('Add New event', 'my-events'),
                'new_item'              => __('New event', 'my-events'),
                'edit_item'             => __('Edit event', 'my-events'),
                'view_item'             => __('View event', 'my-events'),
                'all_items'             => __('All events', 'my-events'),
                'search_items'          => __('Search events', 'my-events'),
                'parent_item_colon'     => __('Parent events:', 'my-events'),
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
            'description'        => 'Event custom post type.',
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'event'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => ['title'],
            'taxonomies'         => [],
            'show_in_rest'       => false
        ]);

        register_post_type('invitee', [
            'labels'             => [
                'name'                  => _x('Invitees', 'Post type general name', 'my-events'),
                'singular_name'         => _x('Invitee', 'Post type singular name', 'my-events'),
                'menu_name'             => _x('Invitees', 'Admin Menu text', 'my-events'),
                'name_admin_bar'        => _x('Invitee', 'Add New on Toolbar', 'my-events'),
                'add_new'               => __('Add New', 'my-events'),
                'add_new_item'          => __('Add New invitee', 'my-events'),
                'new_item'              => __('New invitee', 'my-events'),
                'edit_item'             => __('Edit invitee', 'my-events'),
                'view_item'             => __('View invitee', 'my-events'),
                'all_items'             => __('All invitees', 'my-events'),
                'search_items'          => __('Search invitees', 'my-events'),
                'parent_item_colon'     => __('Parent invitees:', 'my-events'),
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
            'description'        => 'Invitee custom post type.',
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'invitee'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => ['title'],
            'taxonomies'         => [],
            'show_in_rest'       => false,
            'capabilities'       => [
                'publish_posts'       => 'update_core',
                'edit_others_posts'   => 'update_core',
                'delete_posts'        => 'update_core',
                'delete_others_posts' => 'update_core',
                'read_private_posts'  => 'update_core',
                'edit_post'           => 'edit_posts',
                'delete_post'         => 'update_core',
                'read_post'           => 'edit_posts',
            ]
        ]);

        register_post_type('invitee_group', [
            'labels'             => [
                'name'                  => _x('Invitee groups', 'Post type general name', 'my-events'),
                'singular_name'         => _x('Invitee group', 'Post type singular name', 'my-events'),
                'menu_name'             => _x('Invitee groups', 'Admin Menu text', 'my-events'),
                'name_admin_bar'        => _x('Invitee group', 'Add New on Toolbar', 'my-events'),
                'add_new'               => __('Add New', 'my-events'),
                'add_new_item'          => __('Add New invitee group', 'my-events'),
                'new_item'              => __('New invitee group', 'my-events'),
                'edit_item'             => __('Edit invitee group', 'my-events'),
                'view_item'             => __('View invitee group', 'my-events'),
                'all_items'             => __('All invitee groups', 'my-events'),
                'search_items'          => __('Search invitee groups', 'my-events'),
                'parent_item_colon'     => __('Parent invitee groups:', 'my-events'),
                'not_found'             => __('No invitee groups found.', 'my-events'),
                'not_found_in_trash'    => __('No invitee groups found in Trash.', 'my-events'),
                'featured_image'        => _x('Invitee group Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-events'),
                'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'archives'              => _x('Invitee group archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-events'),
                'insert_into_item'      => _x('Insert into invitee group', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-events'),
                'uploaded_to_this_item' => _x('Uploaded to this invitee group', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-events'),
                'filter_items_list'     => _x('Filter invitee groups list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-events'),
                'items_list_navigation' => _x('Invitee groups list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-events'),
                'items_list'            => _x('Invitee groups list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-events'),
            ],
            'description'        => 'Invitee group custom post type.',
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=event',
            'query_var'          => true,
            'rewrite'            => ['slug' => 'invitee-group'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => ['title'],
            'taxonomies'         => [],
            'show_in_rest'       => false
        ]);

        register_post_type('event_location', [
            'labels'             => [
                'name'                  => _x('Event locations', 'Post type general name', 'my-events'),
                'singular_name'         => _x('Event location', 'Post type singular name', 'my-events'),
                'menu_name'             => _x('Event locations', 'Admin Menu text', 'my-events'),
                'name_admin_bar'        => _x('Event location', 'Add New on Toolbar', 'my-events'),
                'add_new'               => __('Add New', 'my-events'),
                'add_new_item'          => __('Add New event location', 'my-events'),
                'new_item'              => __('New event location', 'my-events'),
                'edit_item'             => __('Edit event location', 'my-events'),
                'view_item'             => __('View event location', 'my-events'),
                'all_items'             => __('All event locations', 'my-events'),
                'search_items'          => __('Search event locations', 'my-events'),
                'parent_item_colon'     => __('Parent event locations:', 'my-events'),
                'not_found'             => __('No event locations found.', 'my-events'),
                'not_found_in_trash'    => __('No event locations found in Trash.', 'my-events'),
                'featured_image'        => _x('Event location Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-events'),
                'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-events'),
                'archives'              => _x('Event location archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-events'),
                'insert_into_item'      => _x('Insert into event location', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-events'),
                'uploaded_to_this_item' => _x('Uploaded to this event location', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-events'),
                'filter_items_list'     => _x('Filter event locations list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-events'),
                'items_list_navigation' => _x('Event locations list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-events'),
                'items_list'            => _x('Event locations list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-events'),
            ],
            'description'        => 'Event location custom post type.',
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=event',
            'query_var'          => false,
            'rewrite'            => ['slug' => 'event-location'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => ['title'],
            'taxonomies'         => [],
            'show_in_rest'       => false
        ]);
    }
}
