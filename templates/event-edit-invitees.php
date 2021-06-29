<?php

namespace My\Events;

$event = $args['event'];
$accepted = $event->getInviteesUsers('accepted');
$declined = $event->getInviteesUsers('declined');
$pending  = $event->getInviteesUsers('pending');

if (! $accepted && ! $declined && ! $pending) {
    Helpers::adminNotice(__('No invitees found.', 'my-events'));
    return;
}

echo '<ul>';

foreach ($accepted as $user) {
    $invitee = $event->getInviteeByUser($user->ID);
    printf(
        '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
        esc_url(get_edit_post_link($invitee->ID)),
        esc_html($user->display_name),
        esc_html__('accepted', 'my-events')
    );
}

foreach ($pending as $user) {
    $invitee = $event->getInviteeByUser($user->ID);
    printf(
        '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
        esc_url(get_edit_post_link($invitee->ID)),
        esc_html($user->display_name),
        esc_html__('pending', 'my-events')
    );
}

foreach ($declined as $user) {
    $invitee = $event->getInviteeByUser($user->ID);
    printf(
        '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
        esc_url(get_edit_post_link($invitee->ID)),
        esc_html($user->display_name),
        esc_html__('declined', 'my-events')
    );
}

echo '</ul>';
