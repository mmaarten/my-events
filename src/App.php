<?php

namespace My\Events;

class App
{
    public static function init()
    {
        array_map(function ($class) {
            call_user_func([__NAMESPACE__ . '\\' . $class, 'init']);
        }, [
            'Debug',
            'PostTypes',
            'Fields',
            'AdminMenu',
            'AdminColumns',
            'Assets',
            'Events',
            'PrivateEvents',
            'Notifications',
            'Subscriptions',
            'Emails',
            'Calendar',
            'EventCreator',
            'ICal',
        ]);

        add_action('init', [__CLASS__, 'loadTextdomain'], 0);
    }

    public static function loadTextdomain()
    {
        load_plugin_textdomain('my-events', false, dirname(plugin_basename(MY_EVENTS_PLUGIN_FILE)) . '/languages');
    }
}
