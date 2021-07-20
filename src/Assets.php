<?php

namespace My\Events;

class Assets
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueAdminAssets']);
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueueAdminAssets()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('my-events-admin-script', plugins_url('build/admin-script.js', MY_EVENTS_PLUGIN_FILE), ['jquery'], false, true);
        wp_enqueue_style('my-events-admin-style', plugins_url('build/admin-style.css', MY_EVENTS_PLUGIN_FILE));
    }
}
