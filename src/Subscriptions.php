<?php

namespace My\Events;

use My\Events\Posts\Event;

class Subscriptions
{
    public static function init()
    {
        add_action('template_redirect', [__CLASS__, 'maybeSubscribe']);
    }

    public static function maybeSubscribe()
    {
        if (empty($_POST['my-events'])) {
            return;
        }

        if (! wp_verify_nonce($_POST['my-events'], 'event_subscription_form')) {
            return;
        }

        $event_id = $_POST['_event'];
        $user_id  = $_POST['user'];
        $action   = $_POST['action'];

        if (! in_array($action, ['accept', 'decline'])) {
            return;
        }

        switch ($action) {
            case 'accept':
                $result = self::acceptInvitation($user_id, $event_id);
                break;
            case 'decline':
                $result = self::declineInvitation($user_id, $event_id);
                break;
        }

        if (is_wp_error($result)) {

        }
    }

    public static function form($post = null)
    {
        if (! is_user_logged_in()) {
            return;
        }

        $event = new Event($post);

        $user_id = get_current_user_id();

        $invitee = $event->getInvitee($user_id);

        if (! $invitee) {
            return;
        }

        switch ($invitee->getStatus()) {
            case 'accepted':
                printf('<p>%s</p>', esc_html__('You have accepted this invitation.', 'my-events'));
                break;
            case 'declined':
                printf('<p>%s</p>', esc_html__('You have declined this invitation.', 'my-events'));
                break;
            case 'pending':
                printf('<p>%s</p>', esc_html__('We would like to know if you are comming to this event.', 'my-events'));
                break;
        }

        ?>

        <form method="post">

            <?php wp_nonce_field('event_subscription_form', 'my-events'); ?>

            <input type="hidden" name="_event" value="<?php echo esc_attr($event->ID); ?>">
            <input type="hidden" name="user" value="<?php echo esc_attr($user_id); ?>">

            <?php if ($invitee->getStatus() === 'declined') : ?>
            <input type="hidden" name="action" value="accept">
            <p>
                <input type="submit" value="<?php esc_attr_e('Accept', 'my-events'); ?>">
            </p>
            <?php endif; ?>

            <?php if ($invitee->getStatus() === 'accepted') : ?>
            <input type="hidden" name="action" value="decline">
            <p>
                <input type="submit" value="<?php esc_attr_e('Decline', 'my-events'); ?>">
            </p>
            <?php endif; ?>

            <?php if ($invitee->getStatus() === 'pending') : ?>
            <p>
                <label><input type="radio" name="action" value="decline"> <?php esc_html_e('Decline', 'my-events'); ?></label>
                <label><input type="radio" name="action" value="accept"> <?php esc_html_e('Accept', 'my-events'); ?></label>
            </p>
            <p>
                <input type="submit" value="<?php esc_attr_e('Submit', 'my-events'); ?>">
            </p>
            <?php endif; ?>

        </form>

        <?php
    }

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
