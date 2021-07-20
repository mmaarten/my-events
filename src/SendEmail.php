<?php

namespace My\Events;

use My\Events\Posts\Event;

class SendEmail
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
        add_action('wp_ajax_my_events_event_send_email', [__CLASS__, 'process']);
    }

    /**
     * Add meta boxes
     *
     * @param string $post_type
     */
    public static function addMetaBoxes($post_type)
    {
        add_meta_box('my-events-send-email', __('Send email', 'my-events'), [__CLASS__, 'render'], 'event', 'side');
    }

    /**
     * Render
     *
     * @param WP_Post $post
     */
    public static function render($post)
    {
        $event = new Event($post);

        if (! $event->hasInvitees()) {
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

            <?php

                acf_render_fields([
                    [
                        'key'           => 'my_events_send_email_message_field',
                        'label'         => __('Message', 'my-events'),
                        'instructions'  => __('', 'my-events'),
                        'name'          => 'send_email_message',
                        'value'         => '',
                        'type'          => 'textarea',
                        'rows'          => 4,
                        'default_value' => '',
                        'required'      => false,
                    ],
                ]);

            ?>

            <p>
                <button type="button" class="button my-events-submit"><?php esc_html_e('Send', 'my-events'); ?></button>
            </p>

            <div class="my-events-output"></div>

            <?php echo Helpers::adminNotice(__('Be sure to save the event before sending.', 'my-events'), 'warning', true); ?>

        </div>

        <?php
    }

    /**
     * Process
     */
    public static function process()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        check_admin_referer('event_send_email', MY_EVENTS_NONCE_NAME);

        $event_id = isset($_POST['event']) ? $_POST['event'] : 0;
        $message  = isset($_POST['message']) ? $_POST['message'] : '';
        $message  = trim(stripcslashes($message));

        // Check params

        if (! $event_id || get_post_type($event_id) != 'event') {
            wp_send_json_error(Helpers::getAdminNotice(__('Invalid event.', 'my-events'), 'error', true));
        }

        if (! $message) {
            wp_send_json_error(Helpers::getAdminNotice(__('Message is required.', 'my-events'), 'error', true));
        }

        //

        $event = new Event($event_id);
        $recipients = wp_list_pluck($event->getInviteesUsers(), 'user_email');
        $message    = wpautop(esc_html($message));

        if (! $recipients) {
            wp_send_json_error(Helpers::getAdminNotice(__('No recipients.', 'my-events'), 'error', true));
        }

        // Send emails

        foreach ($recipients as $to) {
            $subject = sprintf(__('Announcement for event: %s.', 'my-events'), $event->post_title);

            $email_message = Helpers::loadTemplate('emails/send-email', [
                'event'   => $event,
                'message' => $message,
            ], true);

            Notifications::sendNotification($to, $subject, $email_message, [], [], $event);
        }

        // Response

        wp_send_json_success(Helpers::getAdminNotice(__('Email send.', 'my-events'), 'success', true));
    }
}
