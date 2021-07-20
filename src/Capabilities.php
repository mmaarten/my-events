<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Capabilities
{
    /**
     * Init
     */
    public static function init()
    {
        add_filter('user_has_cap', [__CLASS__, 'userHasCap'], 10, 4);

        add_action('admin_enqueue_scripts', function () {
            $screen = get_current_screen();

            if (in_array($screen->id, ['event', 'invitee'])) {
                $post = get_post();
                if (is_a($post, 'WP_Post')) {
                    update_option('my_events_capabilities_post_id', $post->ID);
                    return;
                }
            }

            delete_option('my_events_capabilities_post_id');
        });
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

        if (! $object_id) {
            $object_id = get_option('my_events_capabilities_post_id');
            if (! $object_id) {
                return $allcaps;
            }
        }

        if (in_array(get_post_type($object_id), ['event', 'invitee'])) {
            if (get_post_type($object_id) == 'event') {
                $event = new Event($object_id);
            }

            if (get_post_type($object_id) == 'invitee') {
                $invitee = new Invitee($object_id);
                $event = new Event($invitee->getEvent());
            }

            // Allow organisers to edit event.
            $organizers_can_edit = $event->getField('organizers_can_edit');

            if ($organizers_can_edit && $event->isOrganizer($user_id)) {
                $allcaps['edit_others_posts'] = true;
            }
        }

        return $allcaps;
    }
}
