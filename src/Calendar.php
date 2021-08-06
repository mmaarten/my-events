<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Calendar
{
    const SHORTCODE = 'calendar';
    const TRANSIENT = 'my_events_calendar';

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

        // add_action('acf/save_post', function ($post_id) {
        //     if (get_post_type($post_id) == 'event') {
        //         self::sanitizeTransient($post_id);
        //     }
        // });

        add_action('before_delete_post', function ($post_id) {
            if (get_post_type($post_id) == 'event') {
                self::sanitizeTransient($post_id);
            }
        });

        add_action('transition_post_status', function ($new_status, $old_status, $post) {
            if (get_post_type($post) == 'event') {
                if (($old_status == 'publish' || $new_status == 'publish') && $old_status != $new_status) {
                    self::sanitizeTransient($post);
                }
            }
        }, 10, 3);

        add_action('post_updated', function ($post_id, $post_after, $post_before) {
            switch (get_post_type($post_id)) {
                case 'event':
                    foreach (['post_title', 'post_name'] as $field) {
                        if ($post_after->$field !== $post_before->$field) {
                            self::sanitizeTransient($post_id);
                            break;
                        }
                    }
                    break;
            }
        }, 10, 3);

        add_action('added_post_meta', function ($post_id, $meta_key) {
            switch (get_post_type($post_id)) {
                case 'event':
                    if (in_array($meta_key, ['start', 'end', 'all_day', 'private'])) {
                        self::sanitizeTransient($post_id);
                    }
                    break;
                case 'invitee':
                    $invitee = new Invitee($post_id);
                    if (in_array($meta_key, ['status'])) {
                        self::sanitizeTransient($invitee->getEvent());
                    }
                    break;
            }
        }, 10, 2);

        add_action('updated_post_meta', function ($meta_id, $post_id, $meta_key) {
            switch (get_post_type($post_id)) {
                case 'event':
                    if (in_array($meta_key, ['start', 'end', 'all_day', 'private'])) {
                        self::sanitizeTransient($post_id);
                    }
                    break;
                case 'invitee':
                    $invitee = new Invitee($post_id);
                    if (in_array($meta_key, ['status'])) {
                        self::sanitizeTransient($invitee->getEvent());
                    }
                    break;
            }
        }, 10, 3);

        add_action('my_events/invitee_added', function ($invitee, $user_id, $event) {
            self::sanitizeTransient($event->ID);
        }, 10, 3);

        add_action('my_events/invitee_removed', function ($invitee, $user_id, $event) {
            self::sanitizeTransient($event->ID);
        }, 10, 3);
    }

    public static function sanitizeTransient($event_id)
    {
        if (! $event_id || get_post_type($event_id) != 'event') {
            return;
        }

        $event = new Event($event_id);

        $event_start = $event->getStartTime('U');
        $event_end  = $event->getEndTime('U');

        $transient = get_transient(self::TRANSIENT);

        if (! is_array($transient)) {
            $transient = [];
        }

        $sanitized = [];
        foreach ($transient as $key => $events) {
            list($user_id, $start, $end) = explode('|', $key);

            $start = date('U', strtotime($start));
            $end   = date('U', strtotime($end));

            if (Helpers::doDatesOverlap($event_start, $event_end, $start, $end)) {
                continue;
            }

            $sanitized[$key] = $events;
        }

        set_transient(self::TRANSIENT, $sanitized);
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

        // Check cache.

        $key = sprintf('%1$s|%2$s|%3$s', get_current_user_id(), $start, $end);

        $transient = get_transient(self::TRANSIENT);

        if (! is_array($transient)) {
            $transient = [];
        }

        if (isset($transient[$key])) {
            wp_send_json(['events' => $transient[$key]]);
        }

        // Get posts.

        $posts = \My\Events\Model::getCalendarEvents($start, $end, get_current_user_id());

        // Create events.

        $events = [];

        if (! is_wp_error($posts)) {
            foreach ($posts as $post) {
                $events[] = self::createCalendarEvent($post);
            }
        }

        $transient[$key] = $events;

        set_transient(self::TRANSIENT, $transient);

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
