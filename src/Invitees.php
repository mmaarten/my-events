<?php

namespace My\Events;

use My\Events\Posts\Event;

class Invitees
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
        add_meta_box('my-events-invitees', __('Invitees', 'my-events'), [__CLASS__, 'render'], 'event', 'side');
    }

    /**
     * Render
     *
     * @param WP_Post $post
     */
    public static function render($post)
    {
        $event = new Event($post);

        if (! $event->hasInvitees()) {
            Helpers::adminNotice(__('No invitees found.', 'my-events'), 'info', true);
            return;
        }

        $statuses     = Helpers::getInviteeStatuses();
        $status_order = ['accepted', 'declined', 'pending'];
        $statuses     = array_merge(array_flip($status_order), $statuses);

        echo '<ul>';

        foreach ($statuses as $status => $status_display) {
            $users = $event->getInviteesUsers($status, ['orderby' => 'display_name', 'order' => 'ASC']);
            foreach ($users as $user) {
                $invitee = $event->getInviteeByUser($user->ID);
                printf(
                    '<li><a href="%1$s">%2$s</a> <small>(%3$s)</small></li>',
                    get_edit_post_link($invitee->ID),
                    esc_html($user->display_name),
                    esc_html(strtolower($status_display))
                );
            }
        }

        echo '</ul>';
    }
}
