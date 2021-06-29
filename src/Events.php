<?php

namespace My\Events;

use My\Events\Posts\Event;

class Events
{
    public static function init()
    {
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('wp_trash_post', [__CLASS__, 'trashPost']);
        add_action('before_delete_post', [__CLASS__, 'beforeDeletePost']);
    }

    public static function getEvents($args = [])
    {
        return get_posts([
            'post_type'   => 'event',
            'post_status' => 'publish',
            'numberposts' => 999,
        ] + $args);
    }

    public static function getInvitees($args = [])
    {
        return get_posts([
            'post_type'   => 'invitee',
            'post_status' => 'publish',
            'numberposts' => 999,
        ] + $args);
    }

    public static function savePost($post_id)
    {
        if (get_post_type($post_id) === 'event') {
            $event = new Event($post_id);
        }
    }

    public static function trashPost($post_id)
    {
        if (get_post_type($post_id) === 'event') {
            $event = new Event($post_id);
        }
    }

    public static function beforeDeletePost($post_id)
    {
        if (get_post_type($post_id) === 'event') {
            $event = new Event($post_id);
        }
    }
}
