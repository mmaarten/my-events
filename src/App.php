<?php

namespace My\Events;

class App
{
    /**
     * Init
     */
    public static function init()
    {
        array_map(function ($class) {
            call_user_func([__NAMESPACE__ . '\\' . $class, 'init']);
        }, [
            'Debug',
            'AdminMenu',
            'PostTypes',
            'AdminColumns',
            'Fields',
            'Assets',
            'Events',
            'Comments',
            'Subscriptions',
            'Notifications',
            'AdminNotices',
            'PrivateEvents',
            // Extensions
            'Invitees',
            'OverlappingEvents',
            'SendEmail',
            'Calendar',
            'iCal',
            'DuplicatePost',
            'Capabilities',
        ]);

        add_action('init', [__CLASS__, 'loadTextdomain'], 0);
    }

    /**
     * Load textdomain
     */
    public static function loadTextdomain()
    {
        load_plugin_textdomain('my-events', false, dirname(plugin_basename(MY_EVENTS_PLUGIN_FILE)) . '/languages');
    }
}
