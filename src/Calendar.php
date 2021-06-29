<?php

namespace My\Events;

class Calendar
{
    public static function render()
    {
        $options = apply_filters('my_events\calendar_options', []);

        printf('<div id="calendar" data-options="%s"></div>', esc_attr(json_encode($options)));
    }

    public static function enqueueAssets()
    {
        wp_enqueue_style('fullcalendar-main-style');
        wp_enqueue_script('fullcalendar-main-script');
        wp_enqueue_script('fullcalendar-locales-script');
    }
}
