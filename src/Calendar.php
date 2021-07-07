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
            return ob_get_clean();
        });

        add_action('wp_enqueue_scripts', [__CLASS__, 'autoEnqueueAssets']);
        add_action('wp_ajax_my_events_get_events', [__CLASS__, 'getEvents']);
        add_action('wp_ajax_nopriv_my_events_get_events', [__CLASS__, 'getEvents']);
        add_action('wp_ajax_my_events_render_calendar_event_detail', [__CLASS__, 'renderEventDetail']);
        add_action('wp_ajax_nopriv_my_events_render_calendar_event_detail', [__CLASS__, 'renderEventDetail']);
        add_action('save_post', [__CLASS__, 'savePost'], 0);
        add_action('acf/save_post', [__CLASS__, 'acfSavePost'], PHP_INT_MAX);
        add_action('transition_post_status', [__CLASS__, 'transitionPostStatus'], 0, 3);

        add_filter('post_type_link', [__CLASS__, 'updateEventLink'], 10, 3);
    }

    public static function updateEventLink($permalink, $post, $leavename)
    {
        if (self::isActive() && get_post_type($post) == 'event') {
            return self::getEventURL($post->ID);
        }
        return $permalink;
    }

    public static function savePost($post_id)
    {
        static $processed = false;

        if (get_post_type($post_id) == 'event' && ! $processed) {
            // update_post_meta($post_id, 'prev_start', get_field('start', $post_id));
            // update_post_meta($post_id, 'prev_end', get_field('end', $post_id));
            // self::sanitizeTransient($post_id);

            $processed = true;
        }
    }

    public static function acfSavePost($post_id)
    {
        if (get_post_type($post_id) == 'event') {
            //self::sanitizeTransient($post_id);
        }
    }

    public static function transitionPostStatus($new_status, $old_status, $post)
    {
        if (get_post_type($post) == 'event') {
            if ($old_status != $new_status && ($old_status == 'publish' || $new_status == 'publish')) {
                //self::sanitizeTransient($post);
            }
        }
    }

    public static function sanitizeTransient($post_id)
    {
        $transient = get_transient(self::TRANSIENT);

        if (! $transient || ! is_array($transient)) {
            return;
        }

        $event = new Event($post_id);

        $event_start = $event->getStartTime('Y-m-d');
        $event_end   = $event->getEndTime('Y-m-d');

        if (! $event_start || ! $event_end) {
            return;
        }

        $sanitized = [];

        foreach ($transient as $key => $events) {
            list($start, $end) = explode('|', $key);

            $start = date('Y-m-d', strtotime($start));
            $end   = date('Y-m-d', strtotime($end));

            if (Helpers::doDatesIntersect($event_start, $event_end, $start, $end)) {
                continue;
            }

            $sanitized[$key] = $events;
        }

        set_transient(self::TRANSIENT, $sanitized);
    }

    public static function isActive()
    {
        $page_id = self::getPageId();

        return $page_id && get_post_type($page_id) ? true : false;
    }

    public static function getEventURL($post_id)
    {
        $page_id = self::getPageId();

        return sprintf(
            '%1$s#calendar/event/%2$s',
            get_permalink($page_id),
            get_post_field('post_name', $post_id)
        );
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
            'featherlight',
            plugins_url('build/featherlight-script.js', MY_EVENTS_PLUGIN_FILE),
            ['jquery'],
            false,
            true
        );

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

    public static function getEvents()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        $start = $_POST['start'];
        $end   = $_POST['end'];

        // Check transient.

        // $transient_key = sprintf('%s|%s', $start, $end);

        // $transient = get_transient(self::TRANSIENT);

        // if (! is_array($transient)) {
        //     $transient = [];
        // }

        // if (isset($transient[$transient_key])) {
        //     // Cached
        //     $posts = $transient[$transient_key];
        // } else {
            // Not cached
            $posts = Model::getEventsBetween($start, $end);
            // Save to cache
        //     $transient[$transient_key] = $posts;
        //     set_transient(self::TRANSIENT, $transient);
        // }

        $events = [];
        foreach ($posts as $post) {
            $events[] = self::createCalendarEvent($post->ID);
        }

        wp_send_json([
            'events' => $events,
        ]);
    }

    public static function renderEventDetail()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        $post_name = isset($_POST['event']) ? $_POST['event'] : '';

        $event = Helpers::getPostByName($post_name, 'event');

        $the_query = new \WP_Query([
            'p'           => $event->ID,
            'post_type'   => 'event',
            'post_status' => 'publish',
        ]);

        ob_start();

        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                Helpers::loadTemplate('event-detail');
            }
        } else {
        }

        $content = ob_get_clean();

        wp_reset_postdata();

        wp_send_json($content);
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
