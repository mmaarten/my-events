<?php

namespace My\Events;

use My\Events\Posts\Event;

class Comments
{
    public static function init()
    {
        //add_filter('comment_moderation_recipients', [__CLASS__, 'recipients'], 10, 2);
        add_filter('comment_notification_recipients', [__CLASS__, 'notificationRecipients'], 10, 2);
    }

    public static function notificationRecipients($emails, $comment_id)
    {
        $comment = get_comment($comment_id);

        if (get_post_type($comment->comment_post_ID) != 'event') {
            return $emails;
        }

        $event = new Event($comment->comment_post_ID);

        $emails     = array_combine($emails, $emails);
        $organisers = wp_list_pluck($event->getOrganisers(), 'user_email', 'user_email');
        $invitees   = wp_list_pluck($event->getInviteesUsers(), 'user_email', 'user_email');

        $emails = array_merge($emails, $organisers, $invitees);

        // Exclude comment author.
        if ($comment->comment_author_email && isset($emails[$comment->comment_author_email])) {
            unset($emails[$comment->comment_author_email]);
        }

        return array_values($emails);
    }
}
