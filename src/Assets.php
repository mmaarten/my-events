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
        wp_enqueue_script('my-events-admin-script', plugins_url('build/admin-script.js', MY_EVENTS_PLUGIN_FILE), ['jquery'], false, true);

        wp_localize_script('my-events-admin-script', 'MyEvents', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        wp_enqueue_style('my-events-admin-style', plugins_url('build/admin-style.css', MY_EVENTS_PLUGIN_FILE));
    }
}
