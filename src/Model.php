<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Model
{
    /**
     * Get posts
     *
     * @param array $args
     * @return array
     */
    public static function getPosts($args = [])
    {
        return get_posts($args + [
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => apply_filters('my_events/numberposts', 999),
        ]);
    }

    /**
     * Get events
     *
     * @param array $args
     * @return array
     */
    public static function getEvents($args = [])
    {
        return self::getPosts($args + [
            'post_type' => 'event',
        ]);
    }

    /**
     * Get user events
     *
     * @param int   $user_id
     * @param string $start
     * @param string $end
     * @return array
     */
    public static function getUserEvents($user_id, $start, $end)
    {
        remove_action('pre_get_posts', [__NAMESPACE__ . '\PrivateEvents', 'excludePrivateEvents']);

        $events = self::getEventsBetween($start, $end);

        add_action('pre_get_posts', [__NAMESPACE__ . '\PrivateEvents', 'excludePrivateEvents']);

        $return = [];

        foreach ($events as $event) {
            $e = new Event($event);

            if ($e->isMember($user_id) || ! $e->areSubscriptionsEnabled()) {
                $return[] = $event;
            }
        }

        return $return;
    }

    public static function getOrganizerEvents($user_id, $args = [])
    {
        return self::getEvents([
            'meta_query' => [
                [
                    'key'     => 'organizers',
                    'compare' => 'LIKE',
                    'value'   => $user_id,
                ],
            ],
        ] + $args);
    }

    /**
     * Order events by start time
     *
     * @param array  $event_ids
     * @param string $order
     * @param array  $args
     * @return array
     */
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

    /**
     * Exclude events that are over
     *
     * @param array  $event_ids
     * @param array  $args
     * @return array
     */
    public static function excludeEventsThatAreOver($event_ids, $args = [])
    {
        if (! $event_ids) {
            return [];
        }

        return self::getEvents($args + [
            'include' => $event_ids,
            'meta_query' => [
                [
                    'key'     => 'end',
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
     * @param string $offset (hours)
     * @param array  $args
     * @return array
     */
    public static function getEventsBetween($start, $end, $offset = 0, $args = [])
    {
        $offset = floatval($offset);

        if ($offset) {
            $start = strtotime($start) - (60 * 60 * $offset);
            $end   = strtotime($end) + (60 * 60 * $offset);

            $start = date_i18n('Y-m-d H:i:s', $start);
            $end   = date_i18n('Y-m-d H:i:s', $end);
        }

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
     * Get overlapping events
     *
     * @param int    $event_id
     * @param int    $offset
     * @param array  $args
     * @return array
     */
    public static function getOverlappingEvents($event_id, $offset = 0, $args = [])
    {
        $event = new Event($event_id);
        $start = $event->getStartTime('Y-m-d H:i:s');
        $end   = $event->getEndTime('Y-m-d H:i:s');

        return self::getEventsBetween($start, $end, $offset, [
            'exclude'   => [$event->ID],
            'orderby'   => 'meta_value',
            'meta_key'  => 'start',
            'meta_type' => 'DATETIME',
            'order'     => 'DESC',
        ] + $args);
    }

    public static function getEventsByInviteeGroup($post_id, $args = [])
    {
        return self::getEvents($args + [
            'meta_query' => [
                [
                    'key'     => 'invitee_type',
                    'compare' => '=',
                    'value'   => 'group',
                ],
                [
                    'key'     => 'invitee_group',
                    'compare' => '=',
                    'value'   => $post_id,
                ],
            ],
        ]);
    }

    public static function getEventsByLocation($post_id, $args = [])
    {
        return self::getEvents($args + [
            'meta_query' => [
                [
                    'key'     => 'location_type',
                    'compare' => '=',
                    'value'   => 'id',
                ],
                [
                    'key'     => 'location_id',
                    'compare' => '=',
                    'value'   => $post_id,
                ],
            ],
        ]);
    }

    public static function getPrivateEvents($args = [])
    {
        return self::getEvents($args + [
            'meta_key'     => 'private',
            'meta_compare' => '=',
            'meta_value'   => true,
        ]);
    }

    public static function getInvitees($args = [])
    {
        return self::getPosts($args + [
            'post_type' => 'invitee',
        ]);
    }

    public static function getInviteesByEvent($event_id, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'event',
            'meta_compare' => '=',
            'meta_value'   => $event_id,
        ]);
    }

    public static function getInviteesByUser($user_id, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'user',
            'meta_compare' => '=',
            'meta_value'   => $user_id,
        ]);
    }

    public static function getInviteesByStatus($status, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'status',
            'meta_compare' => '=',
            'meta_value'   => $status,
        ]);
    }
}
