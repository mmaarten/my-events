<?php

namespace My\Events\Posts;

use My\Events\Events;

class Event extends Post
{
    public function getDescription()
    {
        return $this->getMeta('description', true);
    }

    public function setDescription($value)
    {
        return $this->updateMeta('description', $value);
    }

    public function getStartTime($format = null)
    {
        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        return date_i18n($format, strtotime($this->getMeta('start', true)));
    }

    public function setStartTime($value)
    {
        return $this->updateMeta('start', $value);
    }

    public function getEndTime($format = null)
    {
        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        return date_i18n($format, strtotime($this->getMeta('end', true)));
    }

    public function setEndTime($value)
    {
        return $this->updateMeta('end', $value);
    }

    public function getTimeFromUntil()
    {
        $start_date = $this->getStartTime(get_option('date_format'));
        $end_date   = $this->getEndTime(get_option('date_format'));

        if ($start_date === $end_date) {
            return sprintf(
                __('%1$s from %2$s until %3$s', 'my-events'),
                $start_date,
                $this->getStartTime(get_option('time_format')),
                $this->getEndTime(get_option('time_format'))
            );
        }

        return sprintf(__('from %1$s until %2$s', 'my-events'), $this->getStartTime(), $this->getEndTime());
    }

    public function getOrganisers($args = [])
    {
        $user_ids = $this->getMeta('organisers', true);

        if ($user_ids && is_array($user_ids)) {
            return get_users([
                'include' => $user_ids,
            ] + $args);
        }
        return [];
    }

    public function setOrganisers($value)
    {
        return $this->updateMeta('organisers', $value);
    }

    public function getInvitees($args = [])
    {
        return Events::getInvitees([
            'meta_key'     => 'event',
            'meta_compare' => '=',
            'meta_value'   => $this->ID,
        ] + $args);
    }

    public function getInviteesByStatus($status, $args = [])
    {
        return $this->getInvitees([
            'meta_query' => [
                [
                    'key'     => 'status',
                    'compare' => '=',
                    'value'   => $status,
                ],
            ],
        ] + $args);
    }

    public function getInviteesByUser($user_id, $args = [])
    {
        return $this->getInvitees([
            'meta_query' => [
                [
                    'key'     => 'user',
                    'compare' => '=',
                    'value'   => $user_id,
                ],
            ],
        ] + $args);
    }

    public function getInviteeByUser($user_id, $args = [])
    {
        $invitee = current($this->getInviteesByUser($user_id));

        if ($invitee) {
            return new Invitee($invitee);
        }

        return null;
    }

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

        if ($user_ids) {
            return get_users([
                'include' => $user_ids,
            ] + $args);
        }

        return [];
    }

    public function getInviteeUser($user_id)
    {
        $invitee = $this->getInviteeByUser($user_id);

        if ($invitee) {
            return get_userdata($invitee->getUser());
        }

        return null;
    }

    public function addInvitee($user_id, $status = 'pending')
    {
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
        $invitee->setUser($user_id);
        $invitee->setEvent($this->ID);
        $invitee->setStatus($status);

        do_action('my_events/invitee_added', $invitee, $invitee->getUser(), $this);

        return $post_id;
    }

    public function updateInvitee($user_id, $status)
    {
        $invitee = $this->getInviteeByUser($user_id);

        if ($invitee) {
            return $invitee->setStatus($status);
        }

        return false;
    }

    public function removeInviteeByUser($user_id)
    {
        $invitee = $this->getInviteeByUser($user_id);

        if ($invitee) {
            return $this->removeInvitee($invitee->ID);
        }

        return false;
    }

    public function removeInvitee($post_id)
    {
        if ($post_id && get_post_type($post_id) === 'invitee') {
            $invitee = new Invitee($post_id);

            do_action('my_events/invitee_removed', $invitee, $invitee->getUser(), $this);

            return wp_delete_post($invitee->ID, true);
        }

        return null;
    }

    public function setInvitees($user_ids, $status = 'pending')
    {
        $processed = [];

        foreach ($user_ids as $user_id) {
            $invitee = $this->getInviteeByUser($user_id);

            if ($invitee) {
                $process_id = $invitee->ID;
            } else {
                $process_id = $this->addInvitee($user_id, $status);
            }

            $processed[$process_id] = true;
        }

        $delete = $this->getInvitees([
            'exclude' => array_keys($processed),
        ]);

        foreach ($delete as $invitee) {
            $this->removeInvitee($invitee->ID);
        }
    }

    public function getParticipants($args = [])
    {
        return $this->getInviteesUsers('accepted', $args);
    }

    public function getLocation()
    {
        return $this->getMeta('location', true);
    }

    public function setLocation($value)
    {
        return $this->updateMeta('location', $value);
    }

    public function isOver()
    {
        return strtotime($this->getEndTime('Y-m-d H:i:s')) < time();
    }

    public function isOrganiser($user_id)
    {
        return in_array($user_id, $this->getOrganisers(['fields' => 'ID']));
    }

    public function isInvitee($user_id)
    {
        return $this->getInviteeByUser($user_id) ? true : false;
    }

    public function isParticipant($user_id)
    {
        return in_array($user_id, $this->getParticipants(['fields' => 'ID']));
    }

    public function isPrivate()
    {
        return $this->getMeta('is_private') ? true : false;
    }

    public function hasAccess($user_id)
    {
        if ($this->isPrivate()) {
            return $this->isOrganiser($user_id) || $this->isInvitee($user_id);
        }

        return true;
    }
}
