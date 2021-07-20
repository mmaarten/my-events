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

        $start = $event->getStartTime('Y-m-d H:i');

        if (! $start) {
            $start = date_i18n('Y-m-d H:i');
        }

        $end = $event->getEndTime('Y-m-d H:i');

        if (! $end) {
            $end = date_i18n('Y-m-d H:i');
        }

        ?>

        <div <?php echo acf_esc_attr($atts); ?>>

            <?php

                acf_render_fields([
                    [
                        'key'            => 'my_events_overlapping_start_field',
                        'label'          => __('Start', 'my-events'),
                        'instructions'   => __('', 'my-events'),
                        'name'           => 'overlapping_start',
                        'value'          => $start,
                        'type'           => 'date_time_picker',
                        'display_format' => get_option('date_format') . ' ' . get_option('time_format'),
                        'return_format'  => 'Y-m-d H:i:s',
                        'first_day'      => get_option('start_of_week', 0),
                        'default_value'  => date_i18n('Y-m-d H:00:00'),
                        'required'       => false,
                    ],
                    [
                        'key'            => 'my_events_overlapping_end_field',
                        'label'          => __('Start', 'my-events'),
                        'instructions'   => __('', 'my-events'),
                        'name'           => 'overlapping_end',
                        'value'          => $end,
                        'type'           => 'date_time_picker',
                        'display_format' => get_option('date_format') . ' ' . get_option('time_format'),
                        'return_format'  => 'Y-m-d H:i:s',
                        'first_day'      => get_option('start_of_week', 0),
                        'default_value'  => date_i18n('Y-m-d H:00:00'),
                        'required'       => false,
                    ],
                    [
                        'key'            => 'my_events_overlapping_offset_field',
                        'label'          => __('Precision', 'my-events'),
                        'instructions'   => __('The amount of hours surrounding the start and end date.', 'my-events'),
                        'name'           => 'overlapping_offset',
                        'type'           => 'number',
                        'min'            => 0,
                        'value'          => 1,
                        'default_value'  => 1,
                        'required'       => false,
                    ]
                ]);

            ?>

            <p>
                <button type="button" class="button my-events-submit"><?php esc_html_e('Search', 'my-events'); ?></button>
            </p>

            <div class="my-events-output"></div>

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

        check_admin_referer('search_overlapping_events', MY_EVENTS_NONCE_NAME);

        $start    = $_POST['start'];
        $end      = $_POST['end'];
        $offset   = $_POST['offset'];
        $event_id = $_POST['event'];

        // Check params

        if (! $start || ! strtotime($start)) {
            wp_send_json_error(Helpers::getAdminNotice(__('Invalid start date.', 'my-events'), 'error', true));
        }

        if (! $end || ! strtotime($end)) {
            wp_send_json_error(Helpers::getAdminNotice(__('Invalid end date.', 'my-events'), 'error', true));
        }

        if (! $event_id || get_post_type($event_id) != 'event') {
            wp_send_json_error(Helpers::getAdminNotice(__('Invalid event.', 'my-events'), 'error', true));
        }

        // Get events

        $events = Model::getEventsBetween($start, $end, $offset, [
            'exclude' => $event_id,
        ]);

        if (! $events) {
            wp_send_json_success(Helpers::getAdminNotice(__('No events found.', 'my-events'), 'error', true));
        }

        // Response

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
