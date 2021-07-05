<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;

class Model
{
    public static function getPosts($args = [])
    {
        return get_posts($args + [
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => 999,
        ]);
    }

    public static function getEvents($args = [])
    {
        return self::getPosts($args + [
            'post_type' => 'event',
        ]);
    }

    public static function orderEventsByStartTime($event_ids, $order = 'ASC', $args = [])
    {
        if (! $event_ids) {
            return [];
        }

        return self::getEvents($args + [
            'include'   => $event_ids,
            'orderby'   => 'meta_value',
            'meta_key'  => 'start',
            'meta_type' => 'DATETIME',
            'order'     => $order,
        ]);
    }

    public static function excludeEventsThatAreOver($event_ids, $args = [])
    {
        if (! $event_ids) {
            return [];
        }

        return self::getEvents($args + [
            'include' => $event_ids,
            'meta_query' => [
                [
                    'key'     => 'start',
                    'compare' => '>=',
                    'value'   => date_i18n('Y-m-d H:i:s'),
                    'type'    => 'DATETIME',
                ],
            ],
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
        return self::getEvents($args + [
            'meta_query' => [
                'relation' => 'OR',
                [
                    'relation' => 'AND',
                    [
                        'key'     => 'start',
                        'compare' => '>=',
                        'value'   => $start,
                        'type'    => 'DATETIME',
                    ],
                    [
                        'key'     => 'start',
                        'compare' => '<=',
                        'value'   => $end,
                        'type'    => 'DATETIME',
                    ],
                ],
                [
                    'relation' => 'AND',
                    [
                        'key'     => 'end',
                        'compare' => '>=',
                        'value'   => $start,
                        'type'    => 'DATETIME',
                    ],
                    [
                        'key'     => 'end',
                        'compare' => '<=',
                        'value'   => $end,
                        'type'    => 'DATETIME',
                    ],
                ],
            ],
        ]);
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
        return self::getEvents($args + [
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
        ]);
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
            'meta_key'     => 'is_private',
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
        return self::getPosts($args + [
            'post_type' => 'invitee',
        ]);
    }

    /**
     * Get invitees by event
     *
     * @param int   $event_id
     * @param array $args
     * @return array
     */
    public static function getInviteesByEvent($event_id, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'event',
            'meta_compare' => '=',
            'meta_value'   => $event_id,
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
}
