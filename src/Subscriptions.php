<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Subscriptions
{
    /**
     * Init
     */
    public static function init()
    {
        add_filter('the_content', function ($return) {
            if (is_singular('event')) {
                ob_start();
                self::form();
                $return .= ob_get_clean();
            }
            return $return;
        });

        add_action('template_redirect', [__CLASS__, 'process']);
    }

    /**
     * Form
     *
     * @param $event mixed
     */
    public static function form($event = null)
    {
        $event = new Event($event);

        if (! is_user_logged_in()) {
            Helpers::alert(__('You need to be logged in in order to subscribe.', 'my-events'), 'danger');
            return;
        }

        $user_id = get_current_user_id();

        if ($event->isOver()) {
            Helpers::alert(__('Event is over. It is no longer possible to subscribe.', 'my-events'), 'danger');
            return;
        }

        if (! $event->areSubscriptionsEnabled()) {
            Helpers::alert(__('Subscriptions are disabled.', 'my-events'), 'danger');
            return;
        }

        if ($event->isPrivate() && ! $event->isMember($user_id)) {
            Helpers::alert(__('This event is private. You are not allowed to subscribe.', 'my-events'), 'danger');
            return;
        }

        $invitee = $event->getInviteeByUser($user_id);

        if (! $invitee) {
            Helpers::alert(__('You need an invitation in order to subscribe.', 'my-events'), 'danger');
            return;
        }

        if ($event->hasMaxParticipants()) {
            Helpers::alert(
                sprintf(
                    __('Available places: %1$s out of %2$s.', 'my-events'),
                    $event->getAvailablePlaces(),
                    $event->getMaxParticipants()
                ),
                'warning'
            );
        }

        $status = $invitee->getStatus();

        if ($status == 'accepted') {
            Helpers::alert(__('You have accepted the invitation.', 'my-events'), 'success');
        }

        if ($status == 'declined') {
            Helpers::alert(__('You have declined the invitation.', 'my-events'), 'danger');
        }

        if ($status == 'pending') {
            Helpers::alert(__('We would like to know if you are comming to the event.', 'my-events'), 'warning');
        }

        $max_reached = $event->hasMaxParticipants() && $event->getAvailablePlaces() == 0;

        $can_accept  = ($status == 'pending' || $status == 'declined') && ! $max_reached;
        $can_decline = $status == 'pending' || $status == 'accepted';

        if (! $can_accept && ! $can_decline) {
            return;
        }

        ?>

        <form id="my-events-subscription-form" method="post">

            <?php wp_nonce_field('subscription_form', MY_EVENTS_NONCE_NAME); ?>
            <input type="hidden" name="invitee" value="<?php echo esc_attr($invitee->ID); ?>">

            <?php

            $onchange = 'jQuery(this).closest(form).trigger("submit");';

            echo '<ul class="list-inline">';

            if ($can_decline) {
                echo '<li class="list-inline-item">';

                printf(
                    '<label class="btn btn-danger"><input type="radio" class="d-none" name="action" value="decline" onchange="%2$s"> %1$s</label>',
                    esc_html__('Declined', 'my-events'),
                    esc_attr($onchange)
                );

                echo '</li>';
            }

            if ($can_accept) {
                echo '<li class="list-inline-item">';

                printf(
                    '<label class="btn btn-success"><input type="radio" class="d-none" name="action" value="accept" onchange="%2$s"> %1$s</label>',
                    esc_html__('Accept', 'my-events'),
                    esc_attr($onchange)
                );

                echo '</li>';
            }

            echo '</ul>';

            ?>

        </form>

        <?php
    }

    /**
     * Process
     */
    public static function process()
    {
        if (empty($_POST[MY_EVENTS_NONCE_NAME])) {
            return;
        }

        if (! wp_verify_nonce($_POST[MY_EVENTS_NONCE_NAME], 'subscription_form')) {
            return;
        }

        $invitee_id = isset($_POST['invitee']) ? $_POST['invitee'] : 0;
        $action     = isset($_POST['action']) ? $_POST['action'] : '';

        // Validation

        if (! is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();

        if (! $invitee_id || get_post_type($invitee_id) != 'invitee') {
            return;
        }

        $invitee = new Invitee($invitee_id);

        if ($invitee->getUser() != $user_id) {
            return;
        }

        $event_id = $invitee->getEvent();

        if (! $event_id || get_post_type($event_id) != 'event') {
            return;
        }

        $event = new Event($event_id);

        if ($event->isOver()) {
            return;
        }

        if (! $event->areSubscriptionsEnabled()) {
            return;
        }

        $max_reached = $event->hasMaxParticipants() && $event->getAvailablePlaces() == 0;

        // Subscribe

        if ($action == 'accept' && $invitee->getStatus() != 'accepted' && ! $max_reached) {
            $invitee->setStatus('accepted');
            do_action('my_events/invitee_accepted', $invitee, $invitee->getUser(), $event);
        }

        if ($action == 'decline' && $invitee->getStatus() != 'declined') {
            $invitee->setStatus('declined');
            do_action('my_events/invitee_declined', $invitee, $invitee->getUser(), $event);
        }
    }
}
