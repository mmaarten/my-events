<?php

namespace My\Events;

class EventsFilter
{
    const SHORTCODE = 'events-filter';

    /**
     * Init
     */
    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'registerAssets'], 0);
        add_action('wp_enqueue_scripts', [__CLASS__, 'autoEnqueueAssets']);

        add_shortcode(self::SHORTCODE, [__CLASS__, 'shortcode']);
    }

    /**
     * Render
     */
    public static function render()
    {
    }

    /**
     * Process
     */
    public static function process()
    {
    }

    /**
     * Shortcode
     */
    public static function shortcode()
    {
        ob_start();
        self::render();
        return ob_get_clean();
    }

    /**
     * Register assets
     */
    public static function registerAssets()
    {
        wp_register_script(
            'my-events-events-filter-script',
            plugins_url('build/events-filter-script.js', MY_EVENTS_PLUGIN_FILE),
            ['jquery'],
            false,
            true
        );

        wp_register_style(
            'my-events-events-filter-style',
            plugins_url('build/events-filter-style.css', MY_EVENTS_PLUGIN_FILE)
        );

        wp_localize_script('my-events-events-filter-scripts', 'MyEventsFilter', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Enqueue assets
     */
    public static function enqueueAssets()
    {
        wp_enqueue_script('my-events-events-filter-script');
        wp_enqueue_style('my-events-events-filter-style');
    }

    /**
     * Auto enqueue assets
     */
    public static function autoEnqueueAssets()
    {
        $post = get_post();

        if (is_a($post, '\WP_Post') && has_shortcode($post->post_content, self::SHORTCODE)) {
            self::enqueueAssets();
        }
    }
}
