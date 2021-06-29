<?php

namespace My\Events;

class Calendar
{
    public static function init()
    {
        add_shortcode('calendar', function () {
            ob_start();
            self::render();
            return ob_get_clean();
        });
    }

    public static function render()
    {
        $options = apply_filters('my_events/calendar_options', []);

        printf('<div id="calendar" data-options="%s"></div>', esc_attr(json_encode($options)));
    }
}
