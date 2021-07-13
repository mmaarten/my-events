<?php

namespace My\Events;

use My\Events\Posts\Event;

class SendEmail
{
    public static function init()
    {
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
        add_action('wp_ajax_my_events_event_send_email', [__CLASS__, 'sendEmail']);
        add_action('wp_ajax_nopriv_my_events_event_send_email', [__CLASS__, 'sendEmail']);
    }

    public static function addMetaBoxes($post_type)
    {
        add_meta_box('my-events-send-email', __('Send email', 'my-events'), [__CLASS__, 'render'], 'event', 'side');
    }

    public static function render($post)
    {
        $event = new Event($post);

        $invitees = $event->getInviteesUsers();

        if (! $invitees) {
            Helpers::adminNotice(__('No invitees found.', 'my-events'), 'info', true);
            return;
        }

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

            <p><strong><?php esc_html_e('Recipients', 'my-events'); ?></strong></p>

            <ul class="my-events-send-email-recipients">
                <?php

                foreach ($invitees as $user) {
                    printf(
                        '<li><label><input type="checkbox" name="recipients[]" value="%1$s" checked="checked"> %2$s</label></li>',
                        esc_attr($user->ID),
                        esc_html($user->display_name)
                    );
                }

                ?>
            </ul>

            <p>
                <a href="#" class="check-all"><?php esc_html_e('Check all', 'my-events'); ?></a> |
                <a href="#" class="uncheck-all"><?php esc_html_e('Uncheck all', 'my-events'); ?></a>
            </p>

            <p>
                <label for="my-events-send-email-message"><strong><?php esc_html_e('Message', 'my-events'); ?></strong></label><br>
                <textarea id="my-events-send-email-message" class="large-text" rows="5"></textarea>
            </p>

            <p>
                <button type="button" class="button my-events-send-email-submit"><?php esc_html_e('Send', 'my-events'); ?></button>
            </p>

            <div class="my-events-send-email-output"></div>

            <?php echo Helpers::adminNotice(__('Be sure to save the event before sending.', 'my-events'), 'warning', true); ?>

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
        $message  = trim(stripcslashes($message));
        $recipients = isset($_POST['recipients']) && is_array($_POST['recipients']) ? $_POST['recipients'] : [];

        if (! $event_id || get_post_type($event_id) !== 'event') {
            wp_send_json_error(Helpers::getAdminNotice(__('Invalid event.', 'my-events'), 'error', true));
        }

        if (! $message) {
            wp_send_json_error(Helpers::getAdminNotice(__('Message is required.', 'my-events'), 'error', true));
        }

        $message = wpautop(esc_html($message));

        $event = new Event($event_id);

        $event_recipients = wp_list_pluck($event->getInviteesUsers(), 'user_email', 'ID');
        $recipients = array_intersect_key($event_recipients, array_flip($recipients));

        if (! $recipients) {
            wp_send_json_error(Helpers::getAdminNotice(__('At least one recipient is required.', 'my-events'), 'error', true));
        }

        foreach ($recipients as $to) {
            $subject = sprintf(__('Announcement for event "%s".', 'my-events'), $event->post_title);

            $email_message = Helpers::loadTemplate('emails/send-email', [
                'event'   => $event,
                'message' => $message,
            ], true);

            Notifications::sendNotification($to, $subject, $email_message, [], [], $event);
        }

        wp_send_json_success(Helpers::getAdminNotice(__('Email send.', 'my-events'), 'success', true));
    }
}
