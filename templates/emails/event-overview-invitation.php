
<p><?php esc_html__('You are invited for following events:', 'my-events'); ?></p>

<?php

foreach ($args['events'] as $event) {
    printf(
        '<p><a href="%1$s">%2$s</a> (%3$s)</p>',
        get_permalink($event->ID),
        esc_html($event->post_title),
        esc_html($event->getTimeFromUntil())
    );
}

?>
