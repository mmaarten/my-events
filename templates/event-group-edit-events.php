<?php

namespace My\Events;

use My\Events\Posts\Event;

if (! $args['events']) {
    echo Helpers::adminNotice(__('No events found.', 'my-events'), 'info', true);
    return;
}

printf('<p>%s</p>', esc_html__('Events created by this group:', 'my-events'));

echo '<ul>';

foreach ($args['events'] as $event) {
    $event = new Event($event);
    printf(
        '<li><a href="%1$s">%2$s</a></li>',
        esc_url(get_edit_post_link($event->ID)),
        esc_html($event->getTimeFromUntil())
    );
}

echo '</ul>';
