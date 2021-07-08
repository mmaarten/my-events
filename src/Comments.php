<?php

namespace My\Events;

use My\Events\Posts\Event;

class Comments
{
    public static function init()
    {
        //add_filter('comment_moderation_recipients', [__CLASS__, 'recipients'], 10, 2);
        add_filter('comment_notification_recipients', [__CLASS__, 'recipients'], 10, 2);
    }

    public static function recipients($emails, $comment_id)
    {
        $comment = get_comment($comment_id);

        if (get_post_type($comment->comment_post_ID) != 'event') {
            return $emails;
        }

        $event = new Event($comment->comment_post_ID);

        $organisers = wp_list_pluck($event->getOrganisers(), 'user_email', 'user_email');

        if ($organisers) {
            $emails = array_merge(array_flip($emails), $organisers);
            $emails = array_keys($emails);
        }

        return $emails;
    }
}
