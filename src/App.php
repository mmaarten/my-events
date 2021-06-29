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
            'AdminMenu',
            'AdminColumns',
            'Events',
        ]);
    }
}
