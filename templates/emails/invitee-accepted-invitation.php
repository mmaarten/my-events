<?php

    $comments = $args['invitee']->getComments();

?>

<p><?php printf(esc_html__('%1$s accepted your invitation for:', 'my-events'), $args['user']->display_name); ?></p>

<p><a href="<?php echo esc_url(get_permalink($args['event']->ID)); ?>"><?php echo esc_html($args['event']->post_title); ?></a></p>

<p><?php printf(esc_html__('Time: %s', 'my-events'), $args['event']->getTimeFromUntil()); ?></p>

<?php if (trim($comments)) : ?>
    <strong><?php echo esc_html_e('Comments', 'my-events'); ?></strong><br />
    <?php echo wpautop(esc_html($comments)); ?>
<?php endif; ?>
