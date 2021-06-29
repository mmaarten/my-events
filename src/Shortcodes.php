<?php

namespace My\Events;

class Shortcodes
{
    public static function init()
    {
        add_shortcode('event-subscription-form', [__CLASS__, 'eventSubscriptionForm']);
    }

    public static function eventSubscriptionForm($atts)
    {
        ob_start();

        Subscriptions::form(654);

        return ob_get_clean();
    }
}
