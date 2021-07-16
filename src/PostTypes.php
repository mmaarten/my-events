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
            'labels'             => self::getLabels(__('Events', 'my-events'), __('Event', 'my-events')),
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
            'labels'             => self::getLabels(__('Invitees', 'my-events'), __('Invitee', 'my-events')),
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
            'capabilities'       => [
                'edit_post'          => 'update_core',
                'read_post'          => 'update_core',
                'delete_post'        => 'update_core',
                'edit_posts'         => 'update_core',
                'edit_others_posts'  => 'update_core',
                'delete_posts'       => 'update_core',
                'publish_posts'      => 'update_core',
                'read_private_posts' => 'update_core'
            ],
        ]);

        register_post_type('invitee_group', [
            'labels'             => self::getLabels(__('Invitee groups', 'my-events'), __('Invitee group', 'my-events')),
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
            'labels'             => self::getLabels(__('Locations', 'my-events'), __('Location', 'my-events')),
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

        register_taxonomy('event_tag', 'event', [
            'hierarchical' => false,
            'labels'                => [
                'name'                       => _x('Tags', 'taxonomy general name', 'my-events'),
                'singular_name'              => _x('Tag', 'taxonomy singular name', 'my-events'),
                'search_items'               => __('Search Tags', 'my-events'),
                'popular_items'              => __('Popular Tags', 'my-events'),
                'all_items'                  => __('All Tags', 'my-events'),
                'parent_item'                => null,
                'parent_item_colon'          => null,
                'edit_item'                  => __('Edit Tag', 'my-events'),
                'update_item'                => __('Update Tag', 'my-events'),
                'add_new_item'               => __('Add New Tag', 'my-events'),
                'new_item_name'              => __('New Tag Name', 'my-events'),
                'separate_items_with_commas' => __('Separate tags with commas', 'my-events'),
                'add_or_remove_items'        => __('Add or remove tags', 'my-events'),
                'choose_from_most_used'      => __('Choose from the most used tags', 'my-events'),
                'not_found'                  => __('No tags found.', 'my-events'),
                'menu_name'                  => __('Tags', 'my-events'),
            ],
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_nav_menus'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => ['slug' => 'event-tag'],
        ]);
    }

    /**
     * Get labels
     *
     * @param string $name
     * @param string $singular_name
     * @return array
     */
    public static function getLabels($name, $singular_name)
    {
        return [
            'name'                  => $name,
            'singular_name'         => $singular_name,
            'menu_name'             => $name,
            'name_admin_bar'        => $singular_name,
            'add_new'               => __('Add New', 'my-events'),
            'add_new_item'          => sprintf(_x('Add New %s', '%s: post type name', 'my-events'), $singular_name),
            'new_item'              => sprintf(_x('New %s', '%s: post type name', 'my-events'), $singular_name),
            'edit_item'             => sprintf(_x('Edit %s', '%s: post type name', 'my-events'), $singular_name),
            'view_item'             => sprintf(_x('View %s', '%s: post type name', 'my-events'), $singular_name),
            'all_items'             => $name,
            'search_items'          => sprintf(_x('Search %s', '%s: post type name', 'my-events'), $name),
            'parent_item_colon'     => sprintf(_x('Parent %s:', '%s: post type name', 'my-events'), $name),
            'not_found'             => sprintf(_x('No %s found.', '%s: post type name', 'my-events'), strtolower($name)),
            'not_found_in_trash'    => sprintf(_x('No %s found in Trash.', '%s: post type name', 'my-events'), strtolower($name)),
            'featured_image'        => sprintf(_x('%s Cover Image', '%s: post type name', 'my-events'), $singular_name),
            'set_featured_image'    => __('Set cover image', 'my-events'),
            'remove_featured_image' => __('Remove cover image', 'my-events'),
            'use_featured_image'    => __('Use as cover image', 'my-events'),
            'archives'              => sprintf(_x('%s archives', '%s: post type name', 'my-events'), $singular_name),
            'insert_into_item'      => sprintf(_x('Insert into %s', '%s: post type name', 'my-events'), strtolower($singular_name)),
            'uploaded_to_this_item' => sprintf(_x('Uploaded to this %s', '%s: post type name', 'my-events'), strtolower($singular_name)),
            'filter_items_list'     => sprintf(_x('Filter %s list', '%s: post type name', 'my-events'), strtolower($name)),
            'items_list_navigation' => sprintf(_x('%s list navigation', '%s: post type name', 'my-events'), $name),
            'items_list'            => sprintf(_x('%s list', '%s: post type name', 'my-events'), $name),
        ];
    }
}
