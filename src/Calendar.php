<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Calendar
{
    const SHORTCODE = 'calendar';

    /**
     * Init
     */
    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'registerAssets'], 0);
        add_action('wp_enqueue_scripts', [__CLASS__, 'autoEnqueueAssets'], 10);
        add_action('wp_ajax_my_events_get_events', [__CLASS__, 'getEvents']);
        add_action('wp_ajax_nopriv_my_events_get_events', [__CLASS__, 'getEvents']);

        add_shortcode(self::SHORTCODE, [__CLASS__, 'shortcode']);
    }

    /**
     * Render
     */
    public static function render()
    {
        $options = apply_filters('my_events/calendar_options', [
            'locale' => substr(get_locale(), 0, 2),
        ]);

        printf(
            '<div id="calendar" data-options="%s"></div>',
            esc_attr(json_encode($options))
        );
    }

    /**
     * Get events
     */
    public static function getEvents()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        $start = $_POST['start'];
        $end   = $_POST['end'];

        // Get posts.

        $posts = \My\Events\Model::getCalendarEvents($start, $end, get_current_user_id());

        // Create events.

        $events = [];

        if (! is_wp_error($posts)) {
            foreach ($posts as $post) {
                $events[] = self::createCalendarEvent($post);
            }
        }

        wp_send_json([
            'events' => $events,
        ]);
    }

    /**
     * Create calendar event
     *
     * @param int $post_id
     * @return array
     */
    public static function createCalendarEvent($post)
    {
        $post = new Event($post);

        $start = new \DateTime($post->getStartTime('Y-m-d H:i:s'));
        $end   = new \DateTime($post->getEndTime('Y-m-d H:i:s'));

        if ($post->isAllDay()) {
            $end->modify('+1 day');
        }

        // Create event
        $event = [
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'start'     => $start->format('Y-m-d\TH:i:s'),
            'end'       => $end->format('Y-m-d\TH:i:s'),
            'url'       => get_permalink($post->ID),
            'allDay'    => $post->isAllDay(),
            'className' => implode(' ', Events::getEventClasses($post->ID)),
        ];

        return $event;
    }

    /**
     * Register assets
     */
    public static function registerAssets()
    {
        wp_register_script(
            'my-events-calendar-script',
            plugins_url('build/calendar-script.js', MY_EVENTS_PLUGIN_FILE),
            ['jquery'],
            false,
            true
        );

        wp_localize_script('my-events-calendar-script', 'MyEventsCalendarSettings', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        wp_register_style(
            'my-events-calendar-style',
            plugins_url('build/calendar-style.css', MY_EVENTS_PLUGIN_FILE)
        );

        wp_register_style(
            'fontawesome',
            plugins_url('build/fontawesome.css', MY_EVENTS_PLUGIN_FILE)
        );
    }

    /**
     * Enqueue assets
     */
    public static function enqueueAssets()
    {
        wp_enqueue_script('my-events-calendar-script');
        wp_enqueue_style('my-events-calendar-style');
        wp_enqueue_style('fontawesome');
    }

    /**
     * Auto enqueue assets
     */
    public static function autoEnqueueAssets()
    {
        $post = get_post();
        if (is_a($post, '\WP_post') && has_shortcode($post->post_content, self::SHORTCODE)) {
            self::enqueueAssets();
        }
    }

    /**
     * Shortcode
     *
     * @return string
     */
    public static function shortcode()
    {
        ob_start();
        self::render();
        return ob_get_clean();
    }
}
