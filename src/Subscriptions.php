<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Subscriptions
{
    public static function init()
    {
        add_action('template_redirect', [__CLASS__, 'process']);

        add_action('wp_ajax_my_events_process_event_subscription', [__CLASS__, 'process']);
        add_action('wp_ajax_nopriv_my_events_process_event_subscription', [__CLASS__, 'process']);
    }

    public static function notices($post = null)
    {
        if (! is_user_logged_in()) {
            printf(
                '<div class="alert alert-danger" role="alert">%s</div>',
                esc_html__('You need to login in order to subscribe to this event.', 'my-events')
            );
            return;
        }

        $event = new Event($post);

        $user_id = get_current_user_id();

        if (! $event->hasAccess($user_id)) {
            printf('<div class="alert alert-danger" role="alert">%s</div>', esc_html__('You have no access to this event.', 'my-events'));
            return;
        }

        if ($event->isOver()) {
            printf('<div class="alert alert-danger" role="alert">%s</div>', esc_html__('The event is over.', 'my-events'));
            return;
        }

        $invitee = $event->getInviteeByUser($user_id);

        if (! $invitee) {
            printf('<div class="alert alert-danger" role="alert">%s</div>', esc_html__('You are not invited to this event.', 'my-events'));
            return;
        }

        $max_reached = $event->isLimitedParticipants() && count($event->getParticipants()) >= $event->getMaxParticipants();

        if ($invitee->getStatus() !== 'accepted' && $max_reached) {
            printf('<div class="alert alert-danger" role="alert">%s</div>', esc_html__('The maximum amount of participants is reached.', 'my-events'));
        }

        if ($invitee->getStatus() === 'accepted') {
            printf('<div class="alert alert-success" role="alert">%s</div>', esc_html__('You have accepted the invitation.', 'my-events'));
        }

        if ($invitee->getStatus() === 'declined') {
            printf('<div class="alert alert-danger" role="alert">%s</div>', esc_html__('You have declined the invitation.', 'my-events'));
        }

        if ($invitee->getStatus() === 'pending') {
            printf('<div class="alert alert-warning" role="alert">%s</div>', esc_html__('We would like to know if you are comming to this event.', 'my-events'));
        }
    }

    public static function form($post = null)
    {
        if (! is_user_logged_in()) {
            return;
        }

        $event = new Event($post);

        $user_id = get_current_user_id();

        if (! $event->hasAccess($user_id)) {
            return;
        }

        if ($event->isOver()) {
            return;
        }

        $invitee = $event->getInviteeByUser($user_id);

        if (! $invitee) {
            return;
        }

        $max_reached = $event->isLimitedParticipants() && count($event->getParticipants()) >= $event->getMaxParticipants();

        $can_accept  = ($invitee->getStatus() == 'pending' || $invitee->getStatus() == 'declined') && !$max_reached;
        $can_decline = $invitee->getStatus() == 'pending' || $invitee->getStatus() == 'accepted';

        ?>

        <form id="event-subscription-form" method="post">

            <?php wp_nonce_field('event_subscription_form', MY_EVENTS_NONCE_NAME); ?>
            <input type="hidden" name="action" value="my_events_process_event_subscription">
            <input type="hidden" name="invitee" value="<?php echo esc_attr($invitee->ID); ?>">

            <ul class="list-inline mb-0 d-table ml-auto">
                <?php if ($can_decline) : ?>
                <li class="list-inline-item"><label class="btn btn-danger mb-0"><input type="radio" class="d-none" name="request" value="decline"><?php esc_attr_e('Decline invitation', 'my-events'); ?></label></li>
                <?php endif; ?>
                <?php if ($can_accept) : ?>
                <li class="list-inline-item"><label class="btn btn-success mb-0"><input type="radio" class="d-none" name="request" value="accept"><?php esc_attr_e('Accept invitation', 'my-events'); ?></label></li>
                <?php endif; ?>
            </ul>

        </form>

        <?php
    }

    public static function process()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        check_ajax_referer('event_subscription_form', MY_EVENTS_NONCE_NAME);

        $invitee_id = isset($_POST['invitee']) ? $_POST['invitee'] : 0;
        $request    = isset($_POST['request']) ? $_POST['request'] : '';

        if (! is_user_logged_in()) {
            wp_send_json_error(__('You need to login in order to subscribe.', 'my-events'));
        }

        if (! $invitee_id || get_post_type($invitee_id) !== 'invitee') {
            wp_send_json_error(__('Invalid invitee.', 'my-events'));
        }

        $invitee = new Invitee($invitee_id);

        $user_id = get_current_user_id();

        if ($user_id != $invitee->getUser()) {
            wp_send_json_error(__('Invalid user.', 'my-events'));
        }

        $event_id = $invitee->getEvent();

        if (! $event_id || get_post_type($event_id) !== 'event') {
            wp_send_json_error(__('Invalid event.', 'my-events'));
        }

        if (! in_array($request, ['accept', 'decline'])) {
            wp_send_json_error(__('Invalid request.', 'my-events'));
        }

        $event = new Event($event_id);

        $result = null;

        if ($request === 'accept') {
            $result = $event->acceptInvitation($user_id);
        }

        if ($request === 'decline') {
            $result = $event->declineInvitation($user_id);
        }

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        if ($request === 'accept') {
            $message = __('You have accepted the invitation.', 'my-events');
        }

        if ($request === 'decline') {
            $message = __('You have declined the invitation.', 'my-events');
        }

        wp_send_json_success($message);
    }
}
