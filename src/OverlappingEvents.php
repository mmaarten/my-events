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
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueAdminAssets']);

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

    public static function process()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        check_admin_referer('search_overlapping_events', MY_EVENTS_NONCE_NAME);

        $start    = $_POST['overlapping_start'];
        $end      = $_POST['overlapping_end'];
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

        $end = new \DateTime($end);
        $end->modify('+1 day');

        $events = Model::getEventsBetween($start, $end->format('Y-m-d'), [
            'exclude' => $event_id,
        ]);

        $response = self::renderEvents($events);

        wp_send_json_success($response);
    }

    public static function renderEvents($events)
    {
        if (! $events) {
            return Helpers::getAdminNotice(__('No events found.', 'my-events'), 'error', true);
        }

        $return = '<ul>';
        foreach ($events as $event) {
            $event = new Event($event);
            $return .= sprintf(
                '<li><a href="%1$s">%2$s</a><br><small>%3$s</small></li>',
                get_permalink($event->ID),
                esc_html($event->post_title),
                esc_html($event->getTimeFromUntil())
            );
        }
        $return .= '</ul>';

        return $return;
    }

    /**
     * Render
     *
     * @param WP_Post $post
     */
    public static function render($post)
    {
        $event = new Event($post);

        $events = Model::getOverlappingEvents($event->ID);

        ?>

        <div class="my-ajax-form" data-action="my_events_get_overlapping_events" data-event="<?php echo esc_attr($event->ID); ?>">

            <?php wp_nonce_field('search_overlapping_events', MY_EVENTS_NONCE_NAME); ?>

            <p>
                <label for="overlapping-events-start"><?php esc_html_e('Start time', 'my-events'); ?></label><br/>
                <?php

                printf('<input %s>', acf_esc_attr([
                    'id'              => 'overlapping-events-start',
                    'type'            => 'text',
                    'class'           => 'large-text my-datepicker',
                    'data-format'     => get_option('date_format'),
                    'data-alt-field'  => '#overlapping-events-start-alt',
                    'data-alt-format' => 'yy-mm-dd',
                    'value'           => $event->getStartTime(get_option('date_format')),
                    'readonly'        => 'readonly',
                ]));

                ?>
                <input type="hidden" id="overlapping-events-start-alt" class="large-text" name="overlapping_start" value="<?php echo esc_attr($event->getStartTime('Y-m-d')); ?>">
            </p>

            <p>
                <label for="overlapping-events-start"><?php esc_html_e('End time', 'my-events'); ?></label><br/>
                <?php

                printf('<input %s>', acf_esc_attr([
                    'id'              => 'overlapping-events-end',
                    'type'            => 'text',
                    'class'           => 'large-text my-datepicker',
                    'data-format'     => get_option('date_format'),
                    'data-alt-field'  => '#overlapping-events-end-alt',
                    'data-alt-format' => 'yy-mm-dd',
                    'value'           => $event->getEndTime(get_option('date_format')),
                    'readonly'        => 'readonly',
                ]));

                ?>
                <input type="hidden" id="overlapping-events-end-alt" class="large-text" name="overlapping_end" value="<?php echo esc_attr($event->getEndTime('Y-m-d')); ?>">
            </p>

            <p>
                <button type="button" class="button my-ajax-form-submit"><?php esc_html_e('Search', 'my-events'); ?></button>
            </p>

            <div class="my-ajax-form-output">
                <?php echo self::renderEvents($events); ?>
            </div>

        </div>

        <?php
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueueAdminAssets()
    {
        $screen = get_current_screen();

        if ($screen->id != 'event') {
            return;
        }
    }
}
