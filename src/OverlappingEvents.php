<?php

namespace My\Events;

use My\Events\Posts\Event;

class OverlappingEvents
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
    }

    /**
     * Add meta boxes
     *
     * @param string $post_type
     */
    public static function addMetaBoxes($post_type)
    {
        add_meta_box(
            'my-events-overlapping-events',
            __('Overlapping events', 'my-events'),
            [__CLASS__, 'render'],
            'event',
            'side'
        );
    }

    /**
     * Render
     *
     * @param WP_Post $post
     */
    public static function render($post)
    {
        $events = Model::getOverlappingEvents($post);

        if (! $events) {
            Helpers::adminNotice(__('No events found.', 'my-events'), 'info', true);
            return;
        }

        echo '<ul>';
        foreach ($events as $event) {
            $event = new Event($event);
            printf(
                '<li><a href="%1$s">%2$s</a><br><small>%3$s</small></li>',
                get_permalink($event->ID),
                esc_html($event->post_title),
                esc_html($event->getTimeFromUntil())
            );
        }
        echo '</ul>';
    }
}
