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
        add_filter('acf/load_value/key=field_60dacb8f3a3ca', [__CLASS__, 'updateInviteesField'], 10, 3);
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
        // Check post type.
        if (get_post_type($post_id) === 'event') {
            // Get event.
            $event = new Event($post_id);

            // Get invitees from settings field
            $invitees = $event->getMeta('invitees', true);

            // Create/delete invitees.
            $event->setInvitees($invitees);

            // Empty settings field.
            $event->updateMeta('invitees', []);
        }
    }

    public static function trashPost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) === 'event') {
            $event = new Event($post_id);
        }
    }

    public static function beforeDeletePost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) === 'event') {
            // Get event.
            $event = new Event($post_id);

            // Remove all invitees.
            $event->setInvitees([]);
        }
    }

    public static function updateInviteesField($value, $post_id, $field)
    {
        if (! $value) {
            // Populates field with invitees comming from our post type.
            $event = new Event($post_id);
            return $event->getInviteesUsers(null, ['fields' => 'ID']);
        }

        // Return.
        return $value;
    }
}
