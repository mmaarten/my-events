<?php

namespace My\Events;

class EventsFilter
{
    /**
     * Init
     */
    public static function init()
    {
        \My\Postloaders\App::registerPostloader(__NAMESPACE__ . '\Postloaders\Events');
    }
}
