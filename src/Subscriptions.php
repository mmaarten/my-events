<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Subscriptions
{
    protected static $message = '';

    public static function init()
    {
        add_action('template_redirect', [__CLASS__, 'process']);

        add_filter('the_content', function ($content) {

            if (is_singular('event')) {
                ob_start();

                self::form();

                $content .= ob_get_clean();
            }

            return $content;
        });
    }

    public static function form($post = null)
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

        if ($event->isOver()) {
            printf('<div class="alert alert-info" role="alert">%s</div>', esc_html__('The event is over.', 'my-events'));
            return;
        }

        $invitee = $event->getInviteeByUser($user_id);

        if (! $invitee) {
            printf('<div class="alert alert-danger" role="alert">%s</div>', esc_html__('You are not invited to this event.', 'my-events'));
            return;
        }

        $can_accept  = $invitee->getStatus() == 'pending' || $invitee->getStatus() == 'declined';
        $can_decline = $invitee->getStatus() == 'pending' || $invitee->getStatus() == 'accepted';

        ?>

        <form id="event-subscription-form" method="post">

            <?php

            // Messages

            if (self::$message) {
                printf('<div class="alert alert-success" role="alert">%s</div>', esc_html(self::$message));
            }

            if ($invitee->getStatus() === 'accepted') {
                printf(
                    '<div class="alert alert-success" role="alert">%s</div>',
                    esc_html__('You have accepted the invitation.', 'my-events')
                );
            }

            if ($invitee->getStatus() === 'declined') {
                printf(
                    '<div class="alert alert-success" role="alert">%s</div>',
                    esc_html__('You have declined the invitation.', 'my-events')
                );
            }

            if ($invitee->getStatus() === 'pending') {
                printf(
                    '<div class="alert alert-warning" role="alert">%s</div>',
                    esc_html__('You would like to know if you are comming to this event.', 'my-events')
                );
            }

            // Fields

            wp_nonce_field('event_subscription_form', 'my-events');

            printf('<input type="hidden" name="invitee" value="%s">', esc_attr($invitee->ID));

            $onchange = "jQuery(this).closest('form').submit();";

            echo '<ul class="list-inline">';

            if ($can_decline) {
                printf(
                    '<li class="list-inline-item"><label class="btn btn-primary"><input type="radio" class="d-none" name="request" value="decline" onchange="%1$s">%2$s</label></li>',
                    $onchange,
                    esc_attr__('Decline')
                );
            }

            if ($can_accept) {
                printf(
                    '<li class="list-inline-item"><label class="btn btn-primary"><input type="radio" class="d-none" name="request" value="accept" onchange="%1$s">%2$s</label></li>',
                    $onchange,
                    esc_attr__('Accept')
                );
            }

            echo '</ul>';

            ?>

        </form>

        <?php
    }

    public static function process()
    {
        if (empty($_POST['my-events'])) {
            return;
        }

        if (! wp_verify_nonce($_POST['my-events'], 'event_subscription_form')) {
            return;
        }

        if (! is_user_logged_in()) {
            return;
        }

        $invitee_id = isset($_POST['invitee']) ? $_POST['invitee'] : 0;
        $request    = isset($_POST['request']) ? $_POST['request'] : '';

        if (! $invitee_id || get_post_type($invitee_id) !== 'invitee') {
            return;
        }

        $invitee = new Invitee($invitee_id);

        $user_id = get_current_user_id();

        if ($user_id != $invitee->getUser()) {
            return;
        }

        $event_id = $invitee->getEvent();

        if (! $event_id || get_post_type($event_id) !== 'event') {
            return;
        }

        $event = new Event($event_id);

        $result = new \WP_Error(__FUNCTION__, __('Invalid request', 'my-events'));

        if ($request === 'accept') {
            $result = $event->acceptInvitation($user_id);
        }

        if ($request === 'decline') {
            $result = $event->declineInvitation($user_id);
        }

        if (is_wp_error($result)) {
            self::$message = $result->get_error_message();
        } else {
            self::$message = '';
        }
    }
}
