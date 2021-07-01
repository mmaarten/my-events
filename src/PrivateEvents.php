<?php

namespace My\Events;

use My\Events\Posts\Event;

class PrivateEvents
{
    public static function init()
    {
        add_action('pre_get_posts', [__CLASS__, 'excludePrivateEvents']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'checkAccessEventEdit']);
        add_action('admin_notices', [__CLASS__, 'adminNotices']);
    }

    public static function excludePrivateEvents($query)
    {
        // Check role

        if (current_user_can('administrator')) {
            return;
        }

        // Check post type.

        if (! in_array('event', (array) $query->get('post_type'))) {
            return;
        }

        remove_action(current_action(), [__CLASS__, __FUNCTION__]);

        $private_events = Model::getPrivateEvents(['fields' => 'ids']);

        add_action(current_action(), [__CLASS__, __FUNCTION__]);

        $exclude = $query->get('post__not_in');

        if (! is_array($exclude)) {
            $exclude = [];
        }

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            foreach ($private_events as $event_id) {
                $event = new Event($event_id);

                if (is_admin() && $user_id == $event->post_author) {
                    continue;
                }

                if (! $event->hasAccess($user_id)) {
                    $exclude[] = $event->ID;
                }
            }
        } else {
            $exclude = $private_events;
        }

        $query->set('post__not_in', $exclude);
    }

    public static function checkAccessEventEdit()
    {
        $screen = get_current_screen();

        if ($screen->id !== 'event') {
            return;
        }

        $event = new Event($_GET['post']);

        if (current_user_can('administrator')) {
            return;
        }

        $user_id = get_current_user_id();

        if ($user_id == $event->post_author) {
            return;
        }

        if ($event->hasAccess($user_id)) {
            return;
        }

        status_header(403);

        wp_die(__('You are not allowed to access this page', 'my-events'));

        exit;
    }

    public static function adminNotices()
    {
        $screen = get_current_screen();

        if ($screen->id === 'event') {
            $event = new Event($_GET['post']);
            if ($event->isPrivate()) {
                echo Helpers::adminNotice(__('This event is only accessible to organisers and invitees of this event.', 'my-events'));
            }
        }
    }
}
