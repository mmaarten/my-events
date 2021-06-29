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
        add_action('transition_post_status', [__CLASS__, 'transitionPostStatus'], 10, 3);

        add_filter('acf/load_value/key=field_60db0c1e15298', [__CLASS__, 'updateInvitiesField'], 10, 3);
        add_filter('acf/load_field/key=field_60daf8ac12fca', [__CLASS__, 'renderInvities'], 10, 2);
    }

    public static function getEvents($args = [])
    {
        return get_posts([
            'post_type'   => 'event',
            'numberposts' => 999,
        ] + $args);
    }

    public static function getInvitees($args = [])
    {
        return get_posts([
            'post_type'   => 'invitee',
            'numberposts' => 999,
        ] + $args);
    }

    public static function savePost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) !== 'event') {
            return;
        }

        // Update invitees
        self::updateInvities($post_id);
    }

    public static function trashPost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) !== 'event') {
            return;
        }

        $event = new Event($post_id);

        if (! $event->isOver() && $event->getMeta('was_published', true)) {
            do_action('my_events/event_cancelled', $event);
        }
    }

    public static function beforeDeletePost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) !== 'event') {
            return;
        }

        // Get event.
        $event = new Event($post_id);

        // Remove all invitees.
        $event->setInvitees([]);
    }

    public static function transitionPostStatus($new_status, $old_status, $post)
    {
        // Check post type.
        if (get_post_type($post) !== 'event') {
            return;
        }

        $event = new Event($post);

        if ($new_status === 'publish' || $old_status === 'publish') {
            $event->updateMeta('was_published', true);
        }
    }

    public static function updateInvities($event_id)
    {
        // Get event.
        $event = new Event($event_id);

        // Get user ids from settings field.
        $user_ids = $event->getMeta('invitees', true);

        // Create invitees
        $event->setInvitees($user_ids);

        // Remove settings.
        $event->deleteMeta('invitees');
    }

    public static function updateInvitiesField($value, $post_id, $field)
    {
        // Stop when value. We need to access it on post save.
        if ($value) {
            return $value;
        }

        // Populates field with invitees from our post type.

        $event = new Event($post_id);

        return $event->getInviteesUsers(null, ['fields' => 'ID']);
    }

    public static function renderInvities($field)
    {
        $screen = get_current_screen();

        if ($screen->id !== 'event') {
            return $field;
        }

        $event = new Event($_GET['post']);

        $field['message'] = Helpers::loadTemplate('event-edit-invitees', [
            'event' => $event,
        ], true);

        return $field;
    }
}
