<?php

namespace My\Events;

class AdminMenu
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'addMenuPage']);
    }

    /**
     * Add menu page
     */
    public static function addMenuPage()
    {
        add_menu_page(
            __('Events', 'my-events'),
            __('Events', 'my-events'),
            'edit_posts',
            'my-events',
            '',
            'dashicons-admin-post',
            40
        );

        add_submenu_page(
            'my-events',
            __('Tags', 'my-events'),
            __('Tags', 'my-events'),
            'edit_posts',
            'edit-tags.php?taxonomy=event_tag',
            '',
            'dashicons-admin-post',
            0
        );


    }
}
