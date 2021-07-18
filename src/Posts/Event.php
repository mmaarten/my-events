<?php

namespace My\Events\Posts;

use My\Events\Model;

class Event extends Post
{
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getField('description');
    }

    /**
     * Get start time
     *
     * @param string $format
     * @return string
     */
    public function getStartTime($format = '')
    {
        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        $time = $this->getField('start');

        if (! $time) {
            return false;
        }

        return date_i18n($format, strtotime($time));
    }

    /**
     * Get end time
     *
     * @param string $format
     * @return string
     */
    public function getEndTime($format = '')
    {
        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        $time = $this->getField('end');

        if (! $time) {
            return false;
        }

        return date_i18n($format, strtotime($time));
    }

    /**
     * Get time from until
     *
     * @return string
     */
    public function getTimeFromUntil()
    {
        $start_date = $this->getStartTime(get_option('date_format'));
        $end_date   = $this->getEndTime(get_option('date_format'));

        if (! $start_date || ! $end_date) {
            return false;
        }

        if ($start_date == $end_date) {
            return sprintf(
                __('%1$s from %2$s until %3$s', 'my-events'),
                $start_date,
                $this->getStartTime(get_option('time_format')),
                $this->getEndTime(get_option('time_format'))
            );
        }

        return sprintf(__('from %1$s until %2$s', 'my-events'), $this->getStartTime(), $this->getEndTime());
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        $type = $this->getField('location_type');

        if ($type == 'custom') {
            return $this->getField('custom_location');
        }

        if ($type == 'id') {
            $location_id = $this->getField('location_id');
            if ($location_id && get_post_type($location_id)) {
                $location = new Post($location_id);
                return $location->getField('address');
            }
        }

        return false;
    }

    /**
     * Are subscriptions enabled
     *
     * @return bool
     */
    public function areSubscriptionsEnabled()
    {
        return $this->getField('enable_subscriptions') ? true : false;
    }

    /**
     * Get invitee default status
     *
     * @return string
     */
    public function getInviteeDefaultStatus()
    {
        return $this->getField('default_invitee_status');
    }

    /**
     * Get organizers
     *
     * @param array $args
     * @return array
     */
    public function getOrganizers($args = [])
    {
        $user_ids = $this->getField('organizers', false);

        if (! $user_ids || ! is_array($user_ids)) {
            return [];
        }

        return get_users(['include' => $user_ids] + $args);
    }

    /**
     * Get invitees
     *
     * @param array $args
     * @return array
     */
    public function getInvitees($args = [])
    {
        return Model::getInviteesByEvent($this->ID, $args);
    }

    /**
     * Get invitees by user
     *
     * @param int   $user_id
     * @param array $args
     * @return array
     */
    public function getInviteesByUser($user_id, $args = [])
    {
        return $this->getInvitees($args + [
            'meta_query' => [
                [
                    'key'     => 'user',
                    'compare' => '=',
                    'value'   => $user_id,
                ],
            ],
        ]);
    }

    /**
     * Get invitee by user
     *
     * @param int $user_id
     * @return mixed
     */
    public function getInviteeByUser($user_id)
    {
        $invitee = current($this->getInviteesByUser($user_id, ['numberposts' => 1]));

        return $invitee ? new Invitee($invitee) : null;
    }

    /**
     * Get invitee by post
     *
     * @param int $invitee_id
     * @return mixed
     */
    public function getInviteeByPost($invitee_id)
    {
        $invitee = current($this->getInvitees([
            'include'     => [$invitee_id],
            'numberposts' => 1,
        ]));

        return $invitee ? new Invitee($invitee) : null;
    }

    /**
     * Get invitees by status
     *
     * @param string $status
     * @param array  $args
     * @return array
     */
    public function getInviteesByStatus($status, $args = [])
    {
        return $this->getInvitees($args + [
            'meta_query' => [
                [
                    'key'     => 'status',
                    'compare' => '=',
                    'value'   => $status,
                ],
            ],
        ]);
    }

    /**
     * Get invitees users
     *
     * @param string $status
     * @param array  $args
     * @return array
     */
    public function getInviteesUsers($status = null, $args = [])
    {
        if ($status) {
            $invitees = $this->getInviteesByStatus($status);
        } else {
            $invitees = $this->getInvitees();
        }

        $user_ids = [];

        foreach ($invitees as $invitee) {
            $invitee = new Invitee($invitee);
            $user_ids[] = $invitee->getUser();
        }

        if (! $user_ids) {
            return [];
        }

        return get_users(['include' => $user_ids] + $args);
    }

    /**
     * Has invitees
     *
     * @return bool
     */
    public function hasInvitees()
    {
        return $this->getInvitees(['numberposts' => 1, 'fields' => 'ids']) ? true : false;
    }

    /**
     * Add invitee
     *
     * @param int     $user_id
     * @param string  $status
     * @return int
     */
    public function addInvitee($user_id, $status = '')
    {
        if (! $status) {
            $status = $this->getInviteeDefaultStatus();
        }

        $invitee = $this->getInviteeByUser($user_id);

        if ($invitee) {
            return $invitee->ID;
        }

        $post_id = wp_insert_post([
            'post_title'   => '',
            'post_content' => '',
            'post_type'    => 'invitee',
            'post_status'  => 'publish',
        ]);

        $invitee = new Invitee($post_id);
        $invitee->setEvent($this->ID);
        $invitee->setUser($user_id);
        $invitee->setStatus($status);

        do_action('my_events/invitee_added', $invitee, $invitee->getUser(), $this);

        return $invitee->ID;
    }

    /**
     * Remove invitee
     *
     * @param int $user_id
     * @return mixed
     */
    public function removeInvitee($user_id)
    {
        $invitee = $this->getInviteeByUser($user_id);

        if (! $invitee) {
            return false;
        }

        return $this->removeInviteeByPost($invitee->ID);
    }

    /**
     * Remove invitee by post
     *
     * @param int $post_id
     * @return mixed
     */
    public function removeInviteeByPost($post_id)
    {
        $invitee = $this->getInviteeByPost($post_id);

        if (! $invitee) {
            return false;
        }

        do_action('my_events/invitee_removed', $invitee, $invitee->getUser(), $this);

        return wp_delete_post($invitee->ID, true);
    }

    /**
     * Set invitees
     *
     * @param int    $user_ids
     * @param string $status
     */
    public function setInvitees($user_ids, $status = '')
    {
        $processed = [];

        foreach ($user_ids as $user_id) {
            $processed[] = $this->addInvitee($user_id, $status);
        }

        $delete = $this->getInvitees([
            'exclude' => $processed,
            'fields'  => 'ids',
        ]);

        foreach ($delete as $invitee_id) {
            $this->removeInviteeByPost($invitee_id);
        }
    }

    /**
     * Get participants
     *
     * @param array $args
     * @return array
     */
    public function getParticipants($args = [])
    {
        return $this->getInviteesUsers('accepted', $args);
    }

    /**
     * Get max participants
     *
     * @return mixed
     */
    public function getMaxParticipants()
    {
        $value = $this->getField('max_participants');

        return $value ? $value : false;
    }

    /**
     * Has max participants
     *
     * @return bool
     */
    public function hasMaxParticipants()
    {
        return $this->getMaxParticipants() !== false;
    }

    /**
     * Get available places
     *
     * @return int
     */
    public function getAvailablePlaces()
    {
        return max(0, $this->getMaxParticipants() - count($this->getParticipants()));
    }

    /**
     * Is organizer
     *
     * @param int $user_id
     * @return bool
     */
    public function isOrganizer($user_id)
    {
        return in_array($user_id, $this->getOrganizers(['fields' => 'ID']));
    }

    /**
     * Is invitee
     *
     * @param int $user_id
     * @return bool
     */
    public function isInvitee($user_id)
    {
        return $this->getInviteeByUser($user_id) ? true : false;
    }

    /**
     * Is participant
     *
     * @param int $user_id
     * @return bool
     */
    public function isParticipant($user_id)
    {
        return in_array($user_id, $this->getParticipants(['fields' => 'ID']));
    }

    /**
     * Is member
     *
     * @param int $user_id
     * @return bool
     */
    public function isMember($user_id)
    {
        return $this->isOrganizer($user_id) || $this->isInvitee($user_id);
    }

    /**
     * Is over
     *
     * @return bool
     */
    public function isOver()
    {
        return $this->getEndTime('U') < date_i18n('U');
    }

    /**
     * Is private
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->getField('private');
    }

    /**
     * Is all day
     *
     * @return bool
     */
    public function isAllDay()
    {
        return $this->getField('all_day') ? true : false;
    }
}
