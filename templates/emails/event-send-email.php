
<?php echo $args['message']; ?>

<p><a href="<?php echo esc_url(get_permalink($args['event']->ID)); ?>"><?php echo esc_html($args['event']->post_title); ?></a></p>

<p><?php printf(esc_html__('Time: %s', 'my-events'), $args['event']->getTimeFromUntil()); ?></p>
