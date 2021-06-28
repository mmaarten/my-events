<?php

namespace My\Events;

class AdminMenu
{
    /**
     * Initialize
     */
    public static function init()
    {
        add_filter('admin_menu', [__CLASS__, 'addMenuPage']);
        add_filter('register_post_type_args', [__CLASS__, 'registerPostTypeArgs'], 10, 2);
    }

    public static function addMenuPage()
    {
        add_menu_page(__('Events', 'my-events'), __('Events', 'my-events'), 'edit_posts', 'my-events', '', 'dashicons-admin-post', 40);
    }

    public static function registerPostTypeArgs($args, $post_type)
    {
        if (in_array($post_type, ['event', 'invitee', 'invitee_group', 'event_location'])) {
            if ($args['show_in_menu']) {
                $args['show_in_menu'] = 'my-events';
                $args['labels']['all_items'] = $args['labels']['name'];
            }
        }

        return $args;
    }
}
