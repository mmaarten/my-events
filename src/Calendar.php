<?php

namespace My\Events;

use My\Events\Posts\Event;

class Calendar
{
    public static function init()
    {
        add_filter('post_type_link', function ($permalink, $post, $leavename) {
            if (self::isActive() && get_post_type($post) === 'event') {
                return self::getEventURL($post->ID);
            }
            return $permalink;
        }, 10, 3);

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

        $posts = Model::getEventsBetween($start, $end);

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

        $start = $post->getStartTime('Y-m-d\TH:i:s');
        $end   = $post->getEndTime('Y-m-d\TH:i:s');

        if ($post->isAllDay()) {
            $end = new \DateTime($end);
            $end->modify('+1 day');
            $end = $end->format('Y-m-d\TH:i:s');
        }

        // Create event
        $event = [
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'start'     => $start,
            'end'       => $end,
            'allDay'    => $post->isAllDay(),
            'url'       => get_permalink($post->ID),
            'className' => implode(' ', Events::getEventClasses($post->ID)),
        ];

        return apply_filters('de_keerkring_theme/calendar_event', $event, $post->ID);
    }
}
