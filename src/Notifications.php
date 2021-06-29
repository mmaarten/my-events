<?php

namespace My\Events;

class Notifications
{
    public static function init()
    {
        add_action('my_events/invitee_accepted_invitation', [__CLASS__, 'sendInviteeAcceptedNotification'], 10, 3);
        add_action('my_events/invitee_declined_invitation', [__CLASS__, 'sendInviteeDeclinedNotification'], 10, 3);
    }

    public static function sendInviteeAcceptedNotification($invitee, $user_id, $event)
    {
        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        if ($event->isOver()) {
            return false;
        }

        $to = wp_list_pluck($event->getOrganisers(), 'user_email');

        $subject = sprintf(
            __('%1$s accepted your invitation for "%2$s".', 'my-events'),
            $user->display_name,
            $event->post_title
        );

        $message = Helpers::loadTemplate('emails/invitee-accepted-invitation', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message);
    }

    public static function sendInviteeDeclinedNotification($invitee, $user_id, $event)
    {
        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        if ($event->isOver()) {
            return false;
        }

        $to = wp_list_pluck($event->getOrganisers(), 'user_email');

        $subject = sprintf(
            __('%1$s declined your invitation for "%2$s".', 'my-events'),
            $user->display_name,
            $event->post_title
        );

        $message = Helpers::loadTemplate('emails/invitee-declined-invitation', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message);
    }

    public static function sendNotification($to, $subject, $message, $headers = [], $attachments = [])
    {
        add_filter('wp_mail_content_type', [__CLASS__, 'mailContentType']);

        $send = wp_mail($to, $subject, $message, $headers, $attachments);

        remove_filter('wp_mail_content_type', [__CLASS__, 'mailContentType']);

        return $send;
    }

    public static function mailContentType()
    {
        return 'text/html';
    }
}
