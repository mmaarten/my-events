<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Notifications
{
    public static function init()
    {
        add_action('my_events/invitee_accepted_invitation', [__CLASS__, 'sendInviteeAcceptedNotification'], 10, 3);
        add_action('my_events/invitee_declined_invitation', [__CLASS__, 'sendInviteeDeclinedNotification'], 10, 3);
        //add_action('my_events/invitee_removed', [__CLASS__, 'sendInviteeRemovedNotification'], 10, 3);
        add_action('init', [__CLASS__, 'maybeSendInviteeInvitationNotification']);
    }

    public static function maybeSendInviteeInvitationNotification()
    {
        // Get all invitees with status 'pending' who has not received an email.

        $invitees = Model::getInvitees([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    [
                        'key'     => 'email_sent',
                        'compare' => '=',
                        'value'   => false,
                    ],
                    [
                        'key'     => 'email_sent',
                        'compare' => '!=',
                        'value'   => true,
                    ],
                    [
                        'key'     => 'email_sent',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
                [
                    [
                        'key'     => 'status',
                        'compare' => '=',
                        'value'   => 'pending',
                    ],
                ],
            ],
        ]);

        foreach ($invitees as $invitee) {
            $invitee = new Invitee($invitee);
            $event = new Event($invitee->getEvent());
            if ($event && $event->post_status === 'publish') {
                // Send email
                self::sendInviteeInvitationNotification($invitee, $invitee->getUser(), $event);
                $invitee->setEmailSent(true);
            }
        }
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

        if ($invitee->getStatus() !== 'accepted') {
            return;
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

        return self::sendNotification($to, $subject, $message, [], [], $event);
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

        if ($invitee->getStatus() !== 'declined') {
            return;
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

        return self::sendNotification($to, $subject, $message, [], [], $event);
    }

    public static function sendInviteeInvitationNotification($invitee, $user_id, $event)
    {
        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        if ($event->isOver()) {
            return false;
        }

        if ($invitee->getStatus() !== 'pending') {
            return false;
        }

        $to = $user->user_email;

        $subject = sprintf(
            __('You are invited for the event: "%1$s".', 'my-events'),
            $event->post_title
        );

        $message = Helpers::loadTemplate('emails/invitee-added-notification', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message, [], [], $event);
    }

    public static function sendInviteeRemovedNotification($invitee, $user_id, $event)
    {
        if ($event->post_status !== 'publish') {
            return;
        }

        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        if ($event->isOver()) {
            return false;
        }

        if ($invitee->getStatus() === 'declined') {
            return false;
        }

        $to = $user->user_email;

        $subject = sprintf(
            __('You are no longer invited for the event: "%1$s".', 'my-events'),
            $event->post_title
        );

        $message = Helpers::loadTemplate('emails/invitee-removed-notification', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message, [], [], $event);
    }

    public static function sendNotification($to, $subject, $message, $headers = [], $attachments = [], $event = null)
    {
        $args = compact('to', 'subject', 'message', 'headers', 'attachments');
        $args = apply_filters('my_events/notification_args', $args, $event);

        add_filter('wp_mail_content_type', [__CLASS__, 'mailContentType']);

        $send = wp_mail($args['to'], $args['subject'], $args['message'], $args['headers'], $args['attachments']);

        remove_filter('wp_mail_content_type', [__CLASS__, 'mailContentType']);

        return $send;
    }

    public static function mailContentType()
    {
        return 'text/html';
    }
}
