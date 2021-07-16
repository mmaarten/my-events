<?php

namespace My\Events;

use My\Events\Posts\Event;

class OverlappingEvents
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);

        add_action('wp_ajax_my_events_get_overlapping_events', [__CLASS__, 'process']);
        add_action('wp_ajax_nopriv_my_events_get_overlapping_events', [__CLASS__, 'process']);
    }

    /**
     * Add meta boxes
     *
     * @param string $post_type
     */
    public static function addMetaBoxes($post_type)
    {
        add_meta_box(
            'my-events-overlapping-events',
            __('Overlapping events', 'my-events'),
            [__CLASS__, 'render'],
            'event',
            'side'
        );
    }

    /**
     * Render
     *
     * @param WP_Post $post
     */
    public static function render($post)
    {
        $event = new Event($post);

        $atts = [
            'class'          => 'my-events-search-overlapping-events',
            'data-action'    => 'my_events_get_overlapping_events',
            'data-event'     => $event->ID,
            'data-noncename' => MY_EVENTS_NONCE_NAME,
            'data-nonce'     => wp_create_nonce('search_overlapping_events'),
        ];

        ?>

        <div <?php echo acf_esc_attr($atts); ?>>

            <p>
                <label for="my-events-overlapping-start"><?php esc_html_e('Start', 'my-events'); ?></label><br>
                <input type="text" id="my-events-overlapping-start" class="large-text" value="<?php echo esc_attr($event->getStartTime('Y-m-d H:i')); ?>">
            </p>

            <p>
                <label for="my-events-overlapping-end"><?php esc_html_e('End', 'my-events'); ?></label><br>
                <input type="text" id="my-events-overlapping-end" class="large-text" value="<?php echo esc_attr($event->getEndTime('Y-m-d H:i')); ?>">
            </p>

            <p>
                <label for="my-events-overlapping-offset"><?php esc_html_e('Hourly offset', 'my-events'); ?></label><br>
                <input type="number" id="my-events-overlapping-offset" class="large-text" value="1">
            </p>

            <p>
                <button type="button" class="button my-events-submit"><?php esc_html_e('Search', 'my-events'); ?></button>
            </p>

            <div class="my-events-output"></div>

        </div>

        <?php
    }


    public static function process()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        check_admin_referer('search_overlapping_events', MY_EVENTS_NONCE_NAME);

        $start    = $_POST['start'];
        $end      = $_POST['end'];
        $offset   = $_POST['offset'];
        $event_id = $_POST['event'];

        if (! $start || ! strtotime($start)) {
            wp_send_json_error(Helpers::getAdminNotice(__('Invalid start date.', 'my-events'), 'error', true));
        }

        if (! $end || ! strtotime($end)) {
            wp_send_json_error(Helpers::getAdminNotice(__('Invalid end date.', 'my-events'), 'error', true));
        }

        if (! $event_id || get_post_type($event_id) != 'event') {
            wp_send_json_error(Helpers::getAdminNotice(__('Invalid event.', 'my-events'), 'error', true));
        }

        $events = Model::getEventsBetween($start, $end, $offset, [
            'exclude' => $event_id,
        ]);

        if (! $events) {
            wp_send_json_success(Helpers::getAdminNotice(__('No events found.', 'my-events'), 'error', true));
        }

        $response = '<ul>';
        foreach ($events as $event) {
            $event = new Event($event);
            $response .= sprintf(
                '<li><a href="%1$s">%2$s</a><br><small>%3$s</small></li>',
                get_permalink($event->ID),
                esc_html($event->post_title),
                esc_html($event->getTimeFromUntil())
            );
        }
        $response .= '</ul>';

        wp_send_json_success($response);
    }
}
