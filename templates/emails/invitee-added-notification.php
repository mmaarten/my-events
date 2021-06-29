
<p><?php esc_html_e('You are invited for following event:', 'my-events'); ?></p>

<p><a href="<?php echo esc_url(get_permalink($args['event']->ID)); ?>"><?php echo esc_html($args['event']->post_title); ?></a></p>

<p><?php printf(esc_html__('Time: %s', 'my-events'), $args['event']->getTimeFromUntil()); ?></p>
