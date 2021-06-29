<?php

$invitee = $args['invitee'];
$user    = $args['user'];
$event   = $args['event'];

?>

<p><?php esc_html_e('Following person accepted your invitation:', 'my-events'); ?></p>

<p><?php echo esc_html($user->display_name); ?></p>

<p><a href="<?php echo esc_url(get_permalink($event->ID)); ?>"><?php echo esc_html($event->post_title); ?></a></p>

<p><?php printf(esc_html__('Time of day: %s', 'my-events'), $event->getTimeFromUntil()); ?></p>
