<?php

namespace My\Events;

use My\Events\Posts\Event;

class AdminNotices
{
    /**
     * init
     */
    public static function init()
    {
        add_action('admin_notices', [__CLASS__, 'adminNotices']);
    }

    public static function adminNotices()
    {
        $screen = get_current_screen();

        if ($screen->base != 'post' || empty($_GET['post'])) {
            return;
        }

        $post_id = $_GET['post'];

        switch ($screen->post_type) {
            case 'event':
                $event = new Event($post_id);

                if ($event->isOver()) {
                    Helpers::adminNotice(__('This event is over.', 'my-events'), 'warning', false, 'clock');
                }

                if ($event->isPrivate()) {
                    Helpers::adminNotice(__('This event is private.', 'my-events'), 'info', false, 'lock');
                }

                break;
        }
    }
}
