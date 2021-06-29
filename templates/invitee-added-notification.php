<?php

$invitee = $args['invitee'];
$user    = $args['user'];
$event   = $args['event'];

$organisers = wp_list_pluck($event->getOrganisers(), 'display_name', 'ID');

?>

<p><?php esc_html_e('You are invited for following event:', 'my-events'); ?></p>

<p><a href="<?php echo esc_url(get_permalink($event->ID)); ?>"><?php echo esc_html($event->post_title); ?></a></p>

<p><?php esc_html_e('Click the link to accept or decline the invitation.', 'my-events'); ?></p>

<p><?php printf(esc_html__('Time of day: %s', 'my-events'), $event->getTimeFromUntil()); ?></p>

<?php if ($organisers) : ?>
<p><?php printf(esc_html__('Organisers: %s', 'my-events'), implode(', ', $organisers)); ?></p>
<?php endif; ?>
