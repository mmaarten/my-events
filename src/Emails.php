<?php

namespace My\Events;

use My\Events\Posts\Event;

class Emails
{
    public static function init()
    {
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
        add_action('wp_ajax_my_events_event_send_email', [__CLASS__, 'sendEmail']);
        add_action('wp_ajax_nopriv_my_events_event_send_email', [__CLASS__, 'sendEmail']);
    }

    public static function addMetaBoxes($post_type)
    {
        if ($post_type !== 'event') {
            return;
        }

        add_meta_box(
            'my-events-send-email',
            __('Send email', 'my-events'),
            [__CLASS__, 'renderMetaBox'],
            $post_type,
            'side'
        );
    }

    public static function renderMetaBox($post)
    {
        $event = new Event($post);

        $atts = [
            'class'          => 'my-events-send-email',
            'data-action'    => 'my_events_event_send_email',
            'data-event'     => $event->ID,
            'data-noncename' => MY_EVENTS_NONCE_NAME,
            'data-nonce'     => wp_create_nonce('event_send_email'),
        ];

        ?>

        <div <?php echo acf_esc_attr($atts); ?>>

            <p><?php esc_html_e('Notify the invitees about any changes of this event.', 'my-events'); ?></p>

            <p>
                <label for="my-events-send-email-message"><strong><?php esc_html_e('Message', 'my-events'); ?></strong></label><br>
                <textarea id="my-events-send-email-message" class="large-text" rows="5"></textarea>
            </p>

            <p>
                <button type="button" class="button my-events-send-email-submit"><?php esc_html_e('Send', 'my-events'); ?></button>
            </p>

            <div class="my-events-send-email-output"></div>

            <?php echo Helpers::adminNotice(__('Be sure to save the event before sending.', 'my-events'), 'info', true); ?>

        </div>

        <?php
    }

    public static function sendEmail()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        check_admin_referer('event_send_email', MY_EVENTS_NONCE_NAME);

        $event_id = isset($_POST['event']) ? $_POST['event'] : 0;
        $message  = isset($_POST['message']) ? $_POST['message'] : '';
        $message  = wpautop(trim(stripcslashes($message)));

        if (! $event_id || get_post_type($event_id) !== 'event') {
            wp_send_json_error(Helpers::adminNotice(__('Invalid event.', 'my-events'), 'error', true));
        }

        if (! $message) {
            wp_send_json_error(Helpers::adminNotice(__('Message is required.', 'my-events'), 'error', true));
        }

        $event = new Event($event_id);

        $recipients = wp_list_pluck($event->getInviteesUsers(), 'user_email', 'ID');

        foreach ($recipients as $to) {
            $subject = sprintf(__('Announcement for event "%s".', 'my-events'), $event->post_title);

            $email_message = Helpers::loadTemplate('emails/event-send-email', [
                'event'   => $event,
                'message' => $message,
            ], true);

            wp_mail($to, $subject, $email_message);
        }

        wp_send_json_success(Helpers::adminNotice(__('Email send.', 'my-events'), 'success', true));
    }
}
