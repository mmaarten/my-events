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
        $screen = get_current_screen();

        // Only show metabox when there are invitees.
        if ($screen->base = 'post' && $screen->post_type == 'event') {
            if ($screen->action == 'add') {
                return;
            }

            if (isset($_GET['post'])) {
                $event = new Event($_GET['post']);

                $invitees = $event->getInvitees();

                if (! $invitees) {
                    return;
                }
            }

            add_meta_box(
                'my-events-send-email',
                __('Send email', 'my-events'),
                [__CLASS__, 'renderMetaBox'],
                $screen,
                'side'
            );
        }
    }

    public static function renderMetaBox($post)
    {
        $event = new Event($post);

        $invitees = $event->getInviteesUsers();

        if (! $invitees) {
            echo Helpers::adminNotice(__('No invitees found.', 'my-events'), 'info', true);
            return;
        }

        $atts = [
            'class'          => 'my-events-send-email',
            'data-action'    => 'my_events_event_send_email',
            'data-event'     => $event->ID,
            'data-noncename' => MY_EVENTS_NONCE_NAME,
            'data-nonce'     => wp_create_nonce('event_send_email'),
        ];

        $iframe_src = add_query_arg([
            'event' => $event->ID,
        ], plugins_url('email-preview.php', MY_EVENTS_PLUGIN_FILE));

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

            <?php echo Helpers::adminNotice(__('Be sure to save the event before sending.', 'my-events'), 'info', true); ?>

            <p><strong><?php esc_html_e('Preview', 'my-events'); ?></strong></p>

            <iframe src="<?php echo esc_url($iframe_src); ?>"></iframe>

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
            wp_send_json_error(Helpers::adminNotice(__('Invalid event.', 'my-events'), 'error', true));
        }

        if (! $message) {
            wp_send_json_error(Helpers::adminNotice(__('Message is required.', 'my-events'), 'error', true));
        }

        $message = wpautop(esc_html($message));

        $event = new Event($event_id);

        $event_recipients = wp_list_pluck($event->getInviteesUsers(), 'user_email', 'ID');
        $recipients = array_intersect_key($event_recipients, array_flip($recipients));

        if (! $recipients) {
            wp_send_json_error(Helpers::adminNotice(__('At least one recipient is required.', 'my-events'), 'error', true));
        }

        foreach ($recipients as $to) {
            $subject = sprintf(__('Announcement for event "%s".', 'my-events'), $event->post_title);

            $email_message = Helpers::loadTemplate('emails/event-send-email', [
                'event'   => $event,
                'message' => $message,
            ], true);

            Notifications::sendNotification($to, $subject, $email_message, [], [], $event);
        }

        wp_send_json_success(Helpers::adminNotice(__('Email send.', 'my-events'), 'success', true));
    }
}
