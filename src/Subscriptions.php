<?php

namespace My\Events;

class Subscriptions
{
    public static function acceptInvitation($user_id, $event_id)
    {
        $event = new Event($event_id);

        if ($event->isOver()) {
            return new WP_Error(__FUNCTION__, __('Event is over.', 'my-events'));
        }

        if (! $event->hasAccess($user_id)) {
            return new WP_Error(__FUNCTION__, __('Access denied.', 'my-events'));
        }

        $invitee = $event->getInvitee($user_id);

        if (! $invitee) {
            return new WP_Error(__FUNCTION__, __('You are not invited.', 'my-events'));
        }

        if ($invitee->getStatus() === 'accepted') {
            return true;
        }

        if ($event->areSubscriptionLimited() && count($event->getParticipants()) >= $event->getMaxSubscriptions()) {
            return new WP_Error(__FUNCTION__, __('Max number of subscriptions reached.', 'my-events'));
        }

        $invitee->setStatus('accepted');

        do_action('my_events/invitee_accepted_invitation', $invitee, $user_id, $event);

        return true;
    }

    public static function declineInvitation($user_id, $event_id)
    {
        $event = new Event($event_id);

        if ($event->isOver()) {
            return new WP_Error(__FUNCTION__, __('Event is over.', 'my-events'));
        }

        if (! $event->hasAccess($user_id)) {
            return new WP_Error(__FUNCTION__, __('Access denied.', 'my-events'));
        }

        $invitee = $event->getInvitee($user_id);

        if (! $invitee) {
            return new WP_Error(__FUNCTION__, __('You are not invited.', 'my-events'));
        }

        if ($invitee->getStatus() === 'declined') {
            return true;
        }

        $invitee->setStatus('declined');

        do_action('my_events/invitee_declined_invitation', $invitee, $user_id, $event);

        return true;
    }
}
