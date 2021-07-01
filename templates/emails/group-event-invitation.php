<?php

namespace My\Events;

use My\Events\Posts\Event;

?>

<p><?php esc_html_e('You are invited for following group event:', 'my-events'); ?></p>

<p><a href="<?php echo esc_url(get_permalink($args['group']->ID)); ?>"><?php echo esc_html($args['group']->post_title); ?></a></p>

<h2><?php esc_html_e('Times', 'my-events') ?></h2>

<ul>
    <?php

    foreach ($args['events'] as $event) {
        $event = new Event($event);

        printf('<li><a href="%1$s">%2$s</a></li>', esc_url(get_permalink($event->ID)), $event->getTimeFromUntil());
    }

    ?>
</ul>
