<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Notifications
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('my_events/invitee_accepted', [__CLASS__, 'sendInviteeAcceptedNotification'], 10, 3);
        add_action('my_events/invitee_declined', [__CLASS__, 'sendInviteeDeclinedNotification'], 10, 3);
        add_action('init', [__CLASS__, 'maybeSendInvitationNotification']);
    }

    /**
     * Maybe send invitation notification
     */
    public static function maybeSendInvitationNotification()
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
                self::sendInvitationNotification($invitee, $invitee->getUser(), $event);
                $invitee->setEmailSent(true);
            }
        }
    }

    /**
     * Send invitee accepted notification
     *
     * @param Invitee $invitee
     * @param int     $user_id
     * @param Event   $event
     * @return bool
     */
    public static function sendInviteeAcceptedNotification($invitee, $user_id, $event)
    {
        if ($event->isOver()) {
            return false;
        }

        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        $to = wp_list_pluck($event->getOrganizers(), 'user_email');

        if (! $to) {
            return false;
        }

        $subject = sprintf(
            // translators: %1$s: user name. %2$s: event name.
            __('%1$s accepted your invitation for: %2$s', 'my-events'),
            $user->display_name,
            $event->post_title
        );

        $message = Helpers::loadTemplate('emails/invitee-accepted', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message, [], [], $event);
    }

    /**
     * Send invitee declined notification
     *
     * @param Invitee $invitee
     * @param int     $user_id
     * @param Event   $event
     * @return bool
     */
    public static function sendInviteeDeclinedNotification($invitee, $user_id, $event)
    {
        if ($event->isOver()) {
            return false;
        }

        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        $to = wp_list_pluck($event->getOrganizers(), 'user_email');

        if (! $to) {
            return false;
        }

        $subject = sprintf(
            // translators: %1$s: user name. %2$s: event name.
            __('%1$s declined your invitation for: %2$s', 'my-events'),
            $user->display_name,
            $event->post_title
        );

        $message = Helpers::loadTemplate('emails/invitee-accepted', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message, [], [], $event);
    }

    /**
     * Send invitation notification
     *
     * @param Invitee $invitee
     * @param int     $user_id
     * @param Event   $event
     * @return bool
     */
    public static function sendInvitationNotification($invitee, $user_id, $event)
    {
        if ($event->isOver()) {
            return false;
        }

        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        $to = $user->user_email;

        $subject = sprintf(
            // translators: %s: event name.
            __('You are invited for event: %s', 'my-events'),
            $user->display_name,
            $event->post_title
        );

        $message = Helpers::loadTemplate('emails/invitation', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message, [], [], $event);
    }

    /**
     * Send notification
     *
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param array  $header
     * @param array  $attachments
     * @param Event  $event
     * @return bool
     */
    public static function sendNotification($to, $subject, $message, $headers, $attachments, $event)
    {
        $args = apply_filters(
            'my_events/notification_args',
            compact('to', 'subject', 'message', 'headers', 'attachments'),
            $event
        );

        add_filter('wp_mail_content_type', [__CLASS__, 'wpMailContentType']);

        $send = wp_mail($args['to'], $args['subject'], $args['message'], $args['headers'], $args['attachments']);

        do_action('my_events/notification_sent', $send, $args);

        remove_filter('wp_mail_content_type', [__CLASS__, 'wpMailContentType']);

        return $send;
    }

    /**
     * WP mail content type
     *
     * @return string
     */
    public static function wpMailContentType()
    {
        return 'text/html';
    }
}
