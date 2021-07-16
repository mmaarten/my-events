<?php

namespace My\Events;

use My\Events\Posts\Event;

class Capabilities
{
    /**
     * Init
     */
    public static function init()
    {
        add_filter('user_has_cap', [__CLASS__, 'userHasCap'], 10, 4);
    }

    /**
     * User has cap
     *
     * @param array   $allcaps
     * @param array   $caps
     * @param array   $args
     * @param WP_User $user
     * @return array
     */
    public static function userHasCap($allcaps, $caps, $args, $user)
    {
        @list($cap, $user_id, $object_id) = $args;

        if ($object_id && get_post_type($object_id) == 'event') {
            $event = new Event($object_id);

            // Allow organisers to edit event.
            $organizers_can_edit = $event->getField('organizers_can_edit');

            if ($organizers_can_edit && $event->isOrganizer($user_id)) {
                $allcaps['edit_others_posts'] = true;
            }
        }

        return $allcaps;
    }
}
