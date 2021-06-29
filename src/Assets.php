<?php

namespace My\Events;

class Assets
{
    public static function init()
    {
        add_action('admin_enqueue_scripts', [__CLASS__, 'adminEnqueueAssets']);
    }

    public static function adminEnqueueAssets()
    {
        wp_enqueue_style('my-events-admin-style', plugins_url('assets/admin.css', MY_EVENTS_PLUGIN_FILE));
    }
}
