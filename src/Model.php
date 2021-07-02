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

    public static function getEventGroups($args = [])
    {
        return get_posts($args + [
            'post_type'   => 'event_group',
            'post_status' => 'publish',
            'numberposts' => 999,
        ]);
    }

    public static function getEventGroupsByInviteeGroup($group_id, $args = [])
    {
        return self::getEventGroups([
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

    public static function getEventGroupsByLocation($location_id, $args = [])
    {
        return self::getEventGroups([
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

    public static function createGroupEvent($group_id, $start, $end)
    {
        $group = new Post($group_id);

        $event = current(Model::getEventsByEventGroupAndTime($group->ID, $start, $end, ['numberposts' => 1]));

        // Don't update when event exists and is over.
        if ($event) {
            $event = new Event($event);
            if ($event->isOver()) {
                return $event->ID;
            }
        }

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
        $event->updateField('description', $group->getField('description', false));
        $event->updateField('organisers', $group->getField('organisers', false));
        $event->updateField('invitees_type', $group->getField('invitees_type', false));
        $event->updateField('invitees_individual', $group->getField('invitees_individual', false));
        $event->updateField('invitees_group', $group->getField('invitees_group', false));
        $event->updateField('limit_subscriptions', $group->getField('limit_subscriptions', false));
        $event->updateField('max_subscriptions', $group->getField('max_subscriptions', false));
        $event->updateField('location_type', $group->getField('location_type', false));
        $event->updateField('location_input', $group->getField('location_input', false));
        $event->updateField('location_id', $group->getField('location_id', false));
        $event->updateField('is_private', $group->getField('is_private', false));
        $event->updateField('start', $start);
        $event->updateField('end', $end);
        $event->updateField('group', $group->ID);

        Events::setInviteesFromSettingsFields($event->ID);

        // Update post name.
        wp_update_post([
            'ID'        => $event->ID,
            'post_name' => sanitize_title($event->post_title . '-' . $event->getStartTime()),
        ]);

        return $event->ID;
    }
}
