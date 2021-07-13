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
            'AdminMenu',
            'PostTypes',
            'AdminColumns',
        ]);
    }
}
