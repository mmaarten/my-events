<?php

namespace My\Events;

class Notifications
{
    public static function init()
    {
        add_action('my_events/invitee_accepted_invitation', [__CLASS__, 'sendInviteeAcceptedNotification'], 10, 3);
        add_action('my_events/invitee_declined_invitation', [__CLASS__, 'sendInviteeDeclinedNotification'], 10, 3);
        add_action('my_events/invitee_added', [__CLASS__, 'sendInviteeAddedNotification'], 10, 3);
        add_action('my_events/invitee_removed', [__CLASS__, 'sendInviteeRemovedNotification'], 10, 3);
        add_action('my_events/event_cancelled', [__CLASS__, 'sendEventCancelledNotification']);
    }

    public static function sendInviteeAcceptedNotification($invitee, $user_id, $event)
    {
        $to = wp_list_pluck($event->getOrganisers(), 'user_email');

        if (! $to) {
            return false;
        }

        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        $subject = sprintf(
            // translators: %1$s: User display name. %2$s Event name.
            __('%1$s accepted your invitation for "%2$s".', 'my-events'),
            $user->display_name,
            $event->post_title
        );

        $message = Helpers::loadTemplate('invitee-accepted-notification', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message);
    }

    public static function sendInviteeDeclinedNotification($invitee, $user_id, $event)
    {
        $to = wp_list_pluck($event->getOrganisers(), 'user_email');

        if (! $to) {
            return false;
        }

        $user = get_userdata($user_id);

        if (! $user) {
            return false;
        }

        $subject = sprintf(
            // translators: %1$s: User display name. %2$s Event name.
            __('%1$s declined your invitation for "%2$s".', 'my-events'),
            $user->display_name,
            $event->post_title
        );

        $message = Helpers::loadTemplate('invitee-declined-notification', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);

        return self::sendNotification($to, $subject, $message);
    }

    public static function sendInviteeAddedNotification($invitee, $user_id, $event)
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
            // translators: %1$s: User display name. %2$s Event name.
            __('You are invited for the event "%1$s".', 'my-events'),
            $event->post_title
        );

        $message = Helpers::loadTemplate('invitee-added-notification', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);
    }

    public static function sendInviteeRemovedNotification($invitee, $user_id, $event)
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
            // translators: %1$s: User display name. %2$s Event name.
            __('You are no longer invited for the event "%1$s".', 'my-events'),
            $event->post_title
        );

        $message = Helpers::loadTemplate('invitee-removed-notification', [
            'invitee' => $invitee,
            'user'    => $user,
            'event'   => $event,
        ], true);
    }

    public static function sendEventCancelledNotification($event)
    {
        if ($event->isOver()) {
            return false;
        }

        $participants = $event->getParticipants();

        if (! $participants) {
            return false;
        }

        foreach ($participants as $user) {
            $to = $user->user_email;

            $subject = sprintf(
                // translators: %1$s: User display name. %2$s Event name.
                __('Event cancelled: "%1$s".', 'my-events'),
                $event->post_title
            );

            $message = Helpers::loadTemplate('event-cancelled', [
                'user'  => $user,
                'event' => $event,
            ], true);

            self::sendNotification($to, $subject, $message);
        }

        return true;
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
