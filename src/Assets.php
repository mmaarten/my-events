<?php

namespace My\Events;

class Assets
{
    public static function init()
    {
        add_action('admin_enqueue_scripts', [__CLASS__, 'adminEnqueueAssets']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueAssets']);
    }

    public static function adminEnqueueAssets()
    {
        wp_enqueue_style('my-events-admin-style', plugins_url('assets/admin.css', MY_EVENTS_PLUGIN_FILE));
    }

    public static function enqueueAssets()
    {
        wp_enqueue_style('fullcalendar-main-style', plugins_url('assets/fullcalendar/main.min.css', MY_EVENTS_PLUGIN_FILE));
        wp_enqueue_script('fullcalendar-main-script', plugins_url('assets/fullcalendar/main.min.js', MY_EVENTS_PLUGIN_FILE));
        wp_enqueue_script('fullcalendar-locales-script', plugins_url('assets/fullcalendar/locales-all.min.js', MY_EVENTS_PLUGIN_FILE));
        wp_enqueue_script('my-events-calendar-script', plugins_url('assets/calendar.js', MY_EVENTS_PLUGIN_FILE));
    }
}
