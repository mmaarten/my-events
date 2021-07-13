<?php

namespace My\Events;

class PrivateEvents
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('pre_get_posts', [__CLASS__, 'excludePrivateEvents']);
    }

    /**
     * Eclude private events
     *
     * @param WP_Query $query
     */
    public static function excludePrivateEvents($query)
    {
        if (current_user_can('administrator')) {
            return;
        }

        if (! in_array('event', (array) $query->get('post_type'))) {
            return;
        }

        remove_action(current_action(), [__CLASS__, __FUNCTION__]);

        $private_events = Model::getPrivateEvents(['fields' => 'ids']);

        add_action(current_action(), [__CLASS__, __FUNCTION__]);

        if (! $private_events) {
            return;
        }

        $exclude = $query->get('post__not_in');

        if (! is_array($exclude)) {
            $exclude = [];
        }

        if (is_user_logged_in()) {
            $current_user_id = get_current_user_id();

            foreach ($private_events as $event_id) {
                $event = new Event($event_id);

                if (is_admin() && $event->post_author == $current_user_id) {
                    continue;
                }

                if ($event->isMember($current_user_id)) {
                    continue;
                }

                $exclude[] = $event->ID;
            }
        } else {
            $exclude = array_merge($exclude, $private_events);
        }

        $query->set('post__not_in', $exclude);
    }
}
