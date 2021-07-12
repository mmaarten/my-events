<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Subscriptions
{
    protected static $message = '';

    public static function init()
    {
        // add_filter('the_content', function ($the_content) {
        //     if (is_singular('event')) {
        //         $event = new Event();
        //         ob_start();
        //         self::form();
        //         $the_content .= ob_get_clean();

        //         $the_content .= sprintf('<a href="%1$s">%2$s</a>', Calendar::getGoToDateURL($event->getStartTime('Y-m-d')), 'Show in calendar');
        //     }
        //     return $the_content;
        // });

        add_action('template_redirect', [__CLASS__, 'process']);
    }

    public static function form($post = null, $action = null)
    {
        if (self::$message) {
            echo self::$message;
        }

        if (! is_user_logged_in()) {
            printf(
                '<div class="alert alert-danger" role="alert">%s</div>',
                esc_html__('You need to login in order to subscribe to this event.', 'my-events')
            );
            return;
        }

        $event = new Event($post);

        $user_id = get_current_user_id();

        if (! $event->areSubscriptionsEnabled()) {
            printf('<div class="alert alert-danger" role="alert">%s</div>', esc_html__('Subscriptions are disabled.', 'my-events'));
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

        if ($event->hasMaxParticipants()) {
            $max_participants = $event->getMaxParticipants();
            $participant_count = count($event->getParticipants());
            $available_places = max(0, $max_participants - $participant_count);

            $text = sprintf(esc_html__('%1$s out of %2$s places available', 'my-events'), $available_places, $max_participants);

            printf(
                '<div class="alert alert-info" role="alert">%s</div>',
                sprintf(esc_html__('Subscriptions are limited: %s.', 'my-events'), $text)
            );
        }

        $max_reached = $event->hasMaxParticipants() && count($event->getParticipants()) >= $event->getMaxParticipants();

        $can_accept  = ($invitee->getStatus() == 'pending' || $invitee->getStatus() == 'declined') && !$max_reached;
        $can_decline = $invitee->getStatus() == 'pending' || $invitee->getStatus() == 'accepted';

        if ($invitee->getStatus() == 'accepted') {
            printf('<div class="alert alert-success" role="alert">%s</div>', esc_html__('You have accepted the invitation.', 'my-events'));
        }

        if ($invitee->getStatus() == 'declined') {
            printf('<div class="alert alert-success" role="alert">%s</div>', esc_html__('You have declined the invitation.', 'my-events'));
        }

        if ($invitee->getStatus() == 'pending') {
            printf('<div class="alert alert-warning" role="alert">%s</div>', esc_html__('We would like to know if you are comming to this event.', 'my-events'));
        }

        ?>

        <form id="event-subscription-form" action="<?php echo $action ? esc_attr($action) : '#event-subscription-form'; ?>" method="post">

            <?php wp_nonce_field('event_subscription_form', MY_EVENTS_NONCE_NAME); ?>
            <input type="hidden" name="invitee" value="<?php echo esc_attr($invitee->ID); ?>">

            <?php if ($can_accept || $can_decline) : ?>
            <div class="form-group">
                <label for="event-subscription-comments"><?php esc_html_e('Comments', 'my-events'); ?></label>
                <textarea id="event-subscription-comments" class="form-control" name="comments"></textarea>
                <small class="form-text text-muted"><?php esc_html_e('Additional comments for the organisers of this event.', 'my-events'); ?></small>
            </div>
            <?php endif; ?>

            <?php if ($can_accept || $can_decline) : ?>
            <ul class="list-inline mb-0">
                <li class="list-inline-item">
                    <?php

                    printf(
                        '<label class="btn btn-danger mb-0%3$s"><input type="radio" class="d-none" name="request" value="decline" onchange="%1$s"%3$s>%2$s</label>',
                        esc_attr("jQuery(this).closest('form').trigger('submit');"),
                        esc_html__('Decline invitation', 'my-events'),
                        $can_decline ? '' : ' disabled'
                    );

                    ?>
                </li>
                <li class="list-inline-item">
                    <?php

                    printf(
                        '<label class="btn btn-success mb-0%3$s"><input type="radio" class="d-none" name="request" value="accept" onchange="%1$s"%3$s>%2$s</label>',
                        esc_attr("jQuery(this).closest('form').trigger('submit');"),
                        esc_html__('Accept invitation', 'my-events'),
                        $can_accept ? '' : ' disabled'
                    );

                    ?>
                </li>
            </ul>
            <?php endif; ?>

        </form>

        <?php
    }

    public static function process()
    {
        if (! isset($_POST[MY_EVENTS_NONCE_NAME])) {
            return;
        }

        if (! wp_verify_nonce($_POST[MY_EVENTS_NONCE_NAME], 'event_subscription_form')) {
            return;
        }

        $invitee_id = isset($_POST['invitee']) ? $_POST['invitee'] : 0;
        $comments   = isset($_POST['comments']) ? trim($_POST['comments']) : '';
        $request    = isset($_POST['request']) ? $_POST['request'] : '';

        if (! is_user_logged_in()) {
            self::$message = sprintf(
                '<div class="alert alert-danger" role="alert">%s</div>',
                esc_html__('You need to login in order to subscribe.', 'my-events')
            );
            return;
        }

        if (! $invitee_id || get_post_type($invitee_id) !== 'invitee') {
            self::$message = sprintf(
                '<div class="alert alert-danger" role="alert">%s</div>',
                esc_html__('Invalid invitee.', 'my-events')
            );
            return;
        }

        $invitee = new Invitee($invitee_id);

        $user_id = get_current_user_id();

        if ($user_id != $invitee->getUser()) {
            self::$message = sprintf(
                '<div class="alert alert-danger" role="alert">%s</div>',
                esc_html__('Invalid user..', 'my-events')
            );
            return;
        }

        $event_id = $invitee->getEvent();

        if (! $event_id || get_post_type($event_id) !== 'event') {
            self::$message = sprintf(
                '<div class="alert alert-danger" role="alert">%s</div>',
                esc_html__('Invalid event.', 'my-events')
            );
            return;
        }

        if (! in_array($request, ['accept', 'decline'])) {
            self::$message = sprintf(
                '<div class="alert alert-danger" role="alert">%s</div>',
                esc_html__('Invalid request.', 'my-events')
            );
            return;
        }

        $event = new Event($event_id);

        $result = null;

        if ($request === 'accept') {
            $result = $event->acceptInvitation($user_id);
            if (! is_wp_error($result)) {
                $invitee->setComments($comments);
            }
        }

        if ($request === 'decline') {
            $result = $event->declineInvitation($user_id);
            if (! is_wp_error($result)) {
                $invitee->setComments($comments);
            }
        }

        if (is_wp_error($result)) {
            self::$message = sprintf(
                '<div class="alert alert-danger" role="alert">%s</div>',
                esc_html($result->get_error_message())
            );
            return;
        }
    }
}
