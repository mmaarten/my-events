<?php

namespace My\Events\Posts;

use My\Events\Events;

class Event extends Post
{
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getMeta('description', true);
    }

    /**
     * Set description
     *
     * @param string $value
     * @return bool
     */
    public function setDescription($value)
    {
        return $this->updateMeta('description', $value);
    }

    /**
     * Get start time
     *
     * @return string
     */
    public function getStartTime($format = null)
    {
        $value = $this->getMeta('start', true);

        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        return date_i18n($format, strtotime($value));
    }

    /**
     * Set start time
     *
     * @param string $value
     * @return bool
     */
    public function setStartTime($value)
    {
        return $this->updateMeta('start', $value);
    }

    /**
     * Get end time
     *
     * @return string
     */
    public function getEndTime($format = null)
    {
        $value = $this->getMeta('end', true);

        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        return date_i18n($format, strtotime($value));
    }

    /**
     * Set end time
     *
     * @param string $value
     * @return bool
     */
    public function setEndTime($value)
    {
        return $this->updateMeta('end', $value);
    }

    public function getTimeFromUntil()
    {
        $start_date = $this->getStartTime(get_option('date_format'));
        $end_date   = $this->getEndTime(get_option('date_format'));

        if ($start_date === $end_date) {
            $return = sprintf(
                __('%1$s from %2$s until %3$s', 'my-events'),
                $start_date,
                $this->getStartTime('H:i'),
                $this->getEndTime('H:i')
            );
        } else {
            $return = sprintf(__('from %1$s until %2$s', 'my-events'), $this->getStartTime(), $this->getEndTime());
        }

        return $return;
    }

    /**
     * Get organisers
     *
     * @return array
     */
    public function getOrganisers($args = [])
    {
        $user_ids = $this->getMeta('organisers', true);

        if (! $user_ids) {
            return [];
        }

        return get_users([
            'include' => $user_ids,
        ] + $args);
    }

    /**
     * Set organisers
     *
     * @param array $value
     * @return bool
     */
    public function setOrganisers($value)
    {
        return $this->updateMeta('organisers', $value);
    }

    public function isOrganiser($user_id)
    {
        return in_array($user_id, $this->getOrganisers(['fields' => 'ID']));
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        $type = $this->getMeta('location_type', true);

        if ($type === 'input') {
            return $this->getMeta('location_input', true);
        }

        if ($type === 'id') {
            $post_id = $this->getMeta('location_id', true);

            if ($post_id && get_post_type($post_id)) {
                $post = new Post($post_id);
                return $post->getMeta('address', true);
            }
        }

        return false;
    }

    /**
     * Set location
     *
     * @param string $value
     * @return bool
     */
    public function setLocation($value, $type = 'input')
    {
        if (! in_array($type, ['id', 'input'])) {
            return false;
        }

        $this->updateMeta('location_type', $type);

        if ($type === 'input') {
            return $this->updateMeta('location_input', $value);
        }

        if ($type === 'id') {
            return $this->updateMeta('location_id', $value);
        }

        return false;
    }

    public function areSubscriptionLimited()
    {
        return $this->getMeta('limit_subscriptions', true) ? true : false;
    }

    public function getMaxSubscriptions()
    {
        return $this->getMeta('max_subscriptions', true);
    }

    public function getInvitee($user_id)
    {
        $post = current($this->getInviteesByUser($user_id));

        return $post ? new Invitee($post) : null;
    }

    public function getInvitees($args = [])
    {
        return Events::getInvitees([
            'meta_key'     => 'event',
            'meta_compare' => '=',
            'meta_value'   => $this->ID,
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

        return get_users([
            'include' => $user_ids,
        ] + $args);
    }

    public function isEnvitee($user_id)
    {
        return $this->getInvitee($user_id) ? true : false;
    }

    public function addInvitee($user_id, $status = 'pending')
    {
        $invitee = $this->getInvitee($user_id);

        if ($invitee) {
            return $invitee->ID;
        }

        $user = get_userdata($user_id);

        $postdata = [
            'post_title'   => $user ? $user->display_name : __('Untitled', 'my-events'),
            'post_content' => '',
            'post_type'    => 'invitee',
            'post_status'  => 'publish',
        ];

        $post_id = wp_insert_post($postdata);

        $invitee = new Invitee($post_id);
        $invitee->setEvent($this->ID);
        $invitee->setUser($user_id);
        $invitee->setStatus($status);

        do_action('my_events/invitee_added', $invitee, $invitee->getUser(), $this);

        return $post_id;
    }

    public function getParticipants($args = [])
    {
        return $this->getInviteesUsers('accepted', $args);
    }

    public function isParticipant($user_id)
    {
        return in_array($user_id, $this->getParticipants(['fields' => 'ID']));
    }

    public function updateInvitee($user_id, $status)
    {
        $invitee = $this->getInvitee($user_id);

        if ($invitee) {
            return $invitee->setStatus($status);
        }

        return false;
    }

    public function removeInvitee($user_id)
    {
        $invitee = $this->getInvitee($user_id);

        if ($invitee) {
            do_action('my_events/invitee_removed', $invitee, $invitee->getUser(), $this);
            wp_delete_post($invitee->ID, true);
        }

        return false;
    }

    public function setInvitees($user_ids, $status = 'pending')
    {
        $processed = [];

        foreach ($user_ids as $user_id) {
            $invitee = $this->getInvitee($user_id);

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
            $invitee = new Invitee($invitee);
            $this->removeInvitee($invitee->getUser());
        }
    }

    public function isOver()
    {
        return strtotime($this->getEndTime('Y-m-d H:i:s')) < time();
    }

    public function isPrivate()
    {
        return $this->getMeta('is_private', true) ? true : false;
    }

    public function hasAccess($user_id)
    {
        if ($this->isPrivate()) {
            return $this->isOrganiser($user_id) || $this->isEnvitee($user_id);
        }

        return true;
    }
}
