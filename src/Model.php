<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;

class Model
{
    /**
     * Get events
     *
     * @param array $args
     * @return array
     */
    public static function getEvents($args = [])
    {
        return get_posts($args + [
            'post_type'   => 'event',
            'post_status' => 'publish',
            'numberposts' => 999,
        ]);
    }

    /**
     * Get events between
     *
     * @param string $start
     * @param string $end
     * @param array  $args
     * @return array
     */
    public static function getEventsBetween($start, $end, $args = [])
    {
        return self::getEvents([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'start',
                    'compare' => '>=',
                    'value'   => $start,
                    'type'    => 'DATETIME',
                ],
                [
                    'key'     => 'end',
                    'compare' => '<=',
                    'value'   => $end,
                    'type'    => 'DATETIME',
                ]
            ],
        ] + $args);
    }

    /**
     * Get events by time
     *
     * @param string $start
     * @param string $end
     * @param array  $args
     * @return array
     */
    public static function getEventsByTime($start, $end, $args = [])
    {
        return self::getEvents([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'start',
                    'compare' => '=',
                    'value'   => $start,
                    'type'    => 'DATETIME',
                ],
                [
                    'key'     => 'end',
                    'compare' => '=',
                    'value'   => $end,
                    'type'    => 'DATETIME',
                ]
            ],
        ] + $args);
    }

    /**
     * Get events by event group
     *
     * @param int   $group_id
     * @param array $args
     * @return array
     */
    public static function getEventsByEventGroup($group_id, $args = [])
    {
        return self::getEvents($args + [
            'meta_key'     => 'group',
            'meta_compare' => '=',
            'meta_value'   => $group_id,
        ]);
    }

    /**
     * Get events by event group and time
     *
     * @param int    $group_id
     * @param string $start
     * @param string $end
     * @param array  $args
     * @return array
     */
    public static function getEventsByEventGroupAndTime($group_id, $start, $end, $args = [])
    {
        $event_ids = self::getEventsByEventGroup($group_id, ['fields' => 'ids']);

        if ($event_ids) {
            return self::getEventsByTime($start, $end, $args + ['include' => $event_ids]);
        }

        return [];
    }

    /**
     * Get events by invitee group
     *
     * @param int   $group_id
     * @param array $args
     * @return array
     */
    public static function getEventsByInviteeGroup($group_id, $args = [])
    {
        return self::getEvents([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'invitees_type',
                    'compare' => '=',
                    'value'   => 'group',
                ],
                [
                    'key'     => 'invitees_group',
                    'compare' => '=',
                    'value'   => $group_id,
                ],
            ],
        ] + $args);
    }

    /**
     * Get events by location
     *
     * @param int   $location_id
     * @param array $args
     * @return array
     */
    public static function getEventsByLocation($location_id, $args = [])
    {
        return self::getEvents([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'location_type',
                    'compare' => '=',
                    'value'   => 'id',
                ],
                [
                    'key'     => 'location_id',
                    'compare' => '=',
                    'value'   => $location_id,
                ],
            ],
        ] + $args);
    }

    /**
     * Get private events
     *
     * @param array $args
     * @return array
     */
    public static function getPrivateEvents($args = [])
    {
        return self::getEvents($args + [
            'meta_key'     => 'private',
            'meta_compare' => '=',
            'meta_value'   => true,
        ]);
    }

    /**
     * Get invitees
     *
     * @param array $args
     * @return array
     */
    public static function getInvitees($args = [])
    {
        return get_posts($args + [
            'post_type'   => 'invitee',
            'post_status' => 'publish',
            'numberposts' => 999,
        ]);
    }

    /**
     * Get invitees by user
     *
     * @param int   $user_id
     * @param array $args
     * @return array
     */
    public static function getInviteesByUser($user_id, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'user',
            'meta_compare' => '=',
            'meta_value'   => $user_id,
        ]);
    }

    /**
     * Get invitees by status
     *
     * @param string $status
     * @param array  $args
     * @return array
     */
    public static function getInviteesByStatus($status, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'status',
            'meta_compare' => '=',
            'meta_value'   => $status,
        ]);
    }

    public static function createGroupEvent($group_id, $start, $end)
    {
        $group = new Post($group_id);

        $event = current(self::getEventsByEventGroupAndTime($group->ID, $start, $end, ['numberposts' => 1]));

        $postdata = [
            'post_title'   => $group->post_title,
            'post_content' => '',
            'post_type'    => 'event',
            'post_status'  => $group->post_status,
        ];

        if ($event) {
            $postdata['ID'] = $event->ID;
        }

        $event_id = wp_insert_post($postdata);

        $event = new Event($event_id);

        // An event group and an event have the same settings.
        $meta_keys = array_keys(get_field_objects($group->ID));
        foreach ($meta_keys as $meta_key) {
            if (! in_array($meta_key, ['start', 'end'])) {
                $event->updateMeta($meta_key, $group->getMeta($meta_key, true));
            }
        }

        $event->updateMeta('start', $start);
        $event->updateMeta('end', $end);
        $event->updateMeta('group', $group->ID);

        Events::setInviteesFromSettingsFields($event->ID);

        return $event->ID;
    }
}
