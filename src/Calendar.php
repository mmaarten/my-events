<?php

namespace My\Events;

use My\Events\Posts\Event;

class Calendar
{
    const TRANSIENT = 'my_events_calendar_events';

    public static function init()
    {
        add_shortcode('calendar', function () {
            ob_start();
            self::render();

            printf('<a href="%1$s">%2$s</a>', self::getGoToDateURL('2021-01-01'), 'go');
            return ob_get_clean();
        });

        add_action('wp_enqueue_scripts', [__CLASS__, 'autoEnqueueAssets']);
        add_action('wp_ajax_my_events_get_events', [__CLASS__, 'getEvents']);
        add_action('wp_ajax_nopriv_my_events_get_events', [__CLASS__, 'getEvents']);
        add_action('acf/save_post', [__CLASS__, 'acfSavePost'], PHP_INT_MAX);
        add_action('transition_post_status', [__CLASS__, 'transitionPostStatus'], 0, 3);
    }

    public static function getGoToDateURL($date)
    {
        $page_id = self::getPageId();

        if (! $page_id) {
            return false;
        }

        return trailingslashit(get_permalink($page_id)) . '#calendar/date/' . date_i18n('Y-m-d', strtotime($date));
    }

    public static function acfSavePost($post_id)
    {
        if (get_post_type($post_id) == 'event') {
            // TODO: Do not delete all.
            delete_transient(self::TRANSIENT);
            error_log('delete_transient');
        }
    }

    public static function transitionPostStatus($new_status, $old_status, $post)
    {
        if (get_post_type($post) == 'event') {
            if ($old_status != $new_status && ($old_status == 'publish' || $new_status == 'publish')) {
                // TODO: Do not delete all.
                delete_transient(self::TRANSIENT);
                error_log('delete_transient');
            }
        }
    }

    public static function isActive()
    {
        $page_id = self::getPageId();

        return $page_id && get_post_type($page_id) ? true : false;
    }

    public static function getPageId()
    {
        return apply_filters('my_events/calendar_page', 2);
    }

    public static function isPage()
    {
        $page_id = self::getPageId();

        return $page_id && (is_page($page_id) || is_single($page_id));
    }

    public static function render()
    {
        $options = apply_filters('my_events/calendar_options', []);

        printf('<div id="calendar" data-options="%s"></div>', esc_attr(json_encode($options)));
    }

    public static function enqueueAssets()
    {
        wp_enqueue_script(
            'my-events-calendar-script',
            plugins_url('build/calendar-script.js', MY_EVENTS_PLUGIN_FILE),
            ['jquery'],
            false,
            true
        );

        wp_localize_script('my-events-calendar-script', 'MyEvents', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        wp_enqueue_style(
            'my-events-calendar-style',
            plugins_url('build/calendar-style.css', MY_EVENTS_PLUGIN_FILE),
        );

        wp_enqueue_style(
            'fontawesome',
            plugins_url('build/fontawesome.css', MY_EVENTS_PLUGIN_FILE),
        );
    }

    public static function autoEnqueueAssets()
    {
        if (! self::isPage()) {
            return;
        }

        self::enqueueAssets();
    }

    public static function getTransientKey($start, $end)
    {
        return sprintf('%s|%s', $start, $end);
    }

    public static function getEventsFromTransient($start, $end)
    {
        $key = self::getTransientKey($start, $end);

        $transient = get_transient(self::TRANSIENT);

        if (! is_array($transient)) {
            $transient = [];
        }

        return isset($transient[$key]) ? $transient[$key] : false;
    }

    public static function saveEventsToTransient($posts, $start, $end)
    {
        $key = self::getTransientKey($start, $end);

        $transient = get_transient(self::TRANSIENT);

        if (! is_array($transient)) {
            $transient = [];
        }

        $transient[$key] = $posts;

        return set_transient(self::TRANSIENT, $transient);
    }

    public static function getEvents()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        $start = $_POST['start'];
        $end   = $_POST['end'];

        $posts = self::getEventsFromTransient($start, $end);

        if (! is_array($posts)) {
            $posts = Model::getEventsBetween($start, $end);
            self::saveEventsToTransient($posts, $start, $end);
        }

        $events = [];
        foreach ($posts as $post) {
            $events[] = self::createCalendarEvent($post->ID);
        }

        wp_send_json([
            'events' => $events,
        ]);
    }

    public static function createCalendarEvent($post_id)
    {
        $post = new Event($post_id);

        $start = new \DateTime($post->getStartTime('Y-m-d H:i:s'));
        $end = new \DateTime($post->getEndTime('Y-m-d H:i:s'));

        if ($post->isAllDay()) {
            $end->modify('+1 day');
        }

        // Create event
        $event = [
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'start'     => $start->format('Y-m-d\TH:i:s'),
            'end'       => $end->format('Y-m-d\TH:i:s'),
            'allDay'    => $post->isAllDay(),
            'url'       => get_permalink($post->ID),
            'className' => implode(' ', Events::getEventClasses($post->ID)),
        ];

        return apply_filters('de_keerkring_theme/calendar_event', $event, $post->ID);
    }
}
