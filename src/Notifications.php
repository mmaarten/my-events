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
        //add_action('my_events/invitee_added', [__CLASS__, 'sendInviteeAddedNotification'], 10, 3);
        add_action('my_events/invitee_removed', [__CLASS__, 'sendInviteeRemovedNotification'], 10, 3);
        //add_action('my_events/event_cancelled', [__CLASS__, 'sendEventCancelledNotification'], 10, 3);
        add_action('init', [__CLASS__, 'maybeSendInviteeInvitationEmail']);
        //add_action('init', [__CLASS__, 'test']);
    }

    // public static function sendEventGroupInvitation($group_id)
    // {
    //     $events = get_posts([
    //         'post_type'   => 'event',
    //         'post_status' => 'publish',
    //         'numberposts' => 999,
    //         'orderby'     => 'meta_value',
    //         'meta_key'    => 'start',
    //         'meta_type'   => 'DATETIME',
    //         'order'       => 'ASC',
    //         'meta_query'  => [
    //             [
    //                 'key'     => 'group',
    //                 'compare' => '=',
    //                 'value'   => $group_id,
    //             ],
    //             [
    //                 'key'     => 'start',
    //                 'compare' => '>=',
    //                 'value'   => date_i18n('Y-m-d H:i:s'),
    //                 'type'    => 'DATETIME',
    //             ],
    //         ],
    //     ]);

    //     if (! $events) {
    //         return false;
    //     }

    //     $group = new Post($group_id);

    //     $user_ids = Model::getInviteesFromSettingsField($group_id);

    //     if (! $user_ids) {
    //         return false;
    //     }

    //     $users = get_users([
    //         'include' => $user_ids,
    //     ]);

    //     if (! $users) {
    //         return false;
    //     }

    //     foreach ($users as $user) {
    //         // Double check if the user is invited for the events comming from the group.
    //         $user_events = [];
    //         foreach ($events as $event) {
    //             $event = new Event($event);
    //             if ($event->isInvitee($user->ID)) {
    //                 $user_events[] = $event;
    //             }
    //         }

    //         if (! $user_events) {
    //             continue;
    //         }

    //         $to = $user->user_email;

    //         $subject = sprintf(
    //             __('You are invited for the group event: "%1$s".', 'my-events'),
    //             $event->post_title
    //         );

    //         $message = Helpers::loadTemplate('emails/group-event-invitation', [
    //             'group' => $group,
    //             'user'  => $user,
    //             'event' => $user_events,
    //         ], true);

    //         self::sendNotification($to, $subject, $message);
    //     }

    //     return true;
    // }

    // public static function test()
    // {
    //     $invitees = Model::getInvitees([
    //         'meta_query' => [
    //             'relation' => 'AND',
    //             [
    //                 'relation' => 'OR',
    //                 [
    //                     'key'     => 'email_sent',
    //                     'compare' => '=',
    //                     'value'   => false,
    //                 ],
    //                 [
    //                     'key'     => 'email_sent',
    //                     'compare' => '!=',
    //                     'value'   => true,
    //                 ],
    //                 [
    //                     'key'     => 'email_sent',
    //                     'compare' => 'NOT EXISTS',
    //                 ],
    //             ],
    //             [
    //                 [
    //                     'key'     => 'status',
    //                     'compare' => '=',
    //                     'value'   => 'pending',
    //                 ],
    //             ],
    //         ],
    //     ]);

    //     $events = [];
    //     foreach ($invitees as $invitee) {
    //         $invitee = new Invitee($invitee);
    //         $event_id = $invitee->getEvent();
    //         $user_id = $invitee->getUser();
    //         $events[$user_id][$event_id] = new Event($event_id);
    //         $invitee->setEmailSent(true);
    //     }

    //     // TODO: sort events.
    //     foreach ($events as $user_id => $data) {
    //         self::sendEventOverviewNotification($user_id, $data);
    //     }
    // }

    // public static function sendEventOverviewNotification($user_id, $events)
    // {
    //     $user = get_userdata($user_id);

    //     if (! $user) {
    //         return false;
    //     }

    //     $to = $user->user_email;

    //     $subject = __('Events you are invited for.', 'my-events');

    //     $message = Helpers::loadTemplate('emails/event-overview-invitation', [
    //         'user'   => $user,
    //         'events' => $events,
    //     ], true);

    //     return self::sendNotification($to, $subject, $message);
    // }

    public static function maybeSendInviteeInvitationEmail()
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
                self::sendInviteeAddedNotification($invitee, $invitee->getUser(), $event);
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

        return self::sendNotification($to, $subject, $message);
    }

    public static function sendInviteeAddedNotification($invitee, $user_id, $event)
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

        return self::sendNotification($to, $subject, $message);
    }

    public static function sendInviteeRemovedNotification($invitee, $user_id, $event)
    {
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

        return self::sendNotification($to, $subject, $message);
    }

    // public static function sendEventCancelledNotification($event)
    // {
    //     if ($event->isOver()) {
    //         return false;
    //     }

    //     $participants = $event->getParticipants();

    //     foreach ($participants as $user) {
    //         $to = $user->user_email;

    //         $subject = sprintf(
    //             __('Event cancelled: "%1$s".', 'my-events'),
    //             $event->post_title
    //         );

    //         $message = Helpers::loadTemplate('emails/event-cancelled', [
    //             'user'  => $user,
    //             'event' => $event,
    //         ], true);

    //         self::sendNotification($to, $subject, $message);
    //     }

    //     return true;
    // }

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
