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
     * Set description
     *
     * @param string $value
     * @return bool
     */
    public function setDescription($value)
    {
        return $this->updateField('description', $value);
    }

    /**
     * Get start time
     *
     * @return string
     */
    public function getStartTime($format = null)
    {
        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        return date_i18n($format, strtotime($this->getField('start')));
    }

    /**
     * Set start time
     *
     * @param string $value
     * @return bool
     */
    public function setStartTime($value)
    {
        return $this->updateField('start', $value);
    }

    /**
     * Get end time
     *
     * @return string
     */
    public function getEndTime($format = null)
    {
        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        return date_i18n($format, strtotime($this->getField('end')));
    }

    /**
     * Set end time
     *
     * @param string $value
     * @return bool
     */
    public function setEndTime($value)
    {
        return $this->updateField('end', $value);
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

        if ($start_date == $end_date) {
            return sprintf(
                __('%1$s from %2$s until %3$s', 'my-events'),
                $start_date,
                $this->getStartTime(get_option('time_format')),
                $this->getEndTime(get_option('time_format'))
            );
        }

        return sprintf(
            __('from %1$s until %2$s', 'my-events'),
            $this->getStartTime(),
            $this->getEndTime()
        );
    }

    /**
     * Get organisers
     *
     * @return array
     */
    public function getOrganisers($args = [])
    {
        $user_ids = $this->getField('organisers');

        if (! $user_ids || !is_array($user_ids)) {
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
        return $this->updateField('organisers', $value);
    }

    public function isOrganiser($user_id)
    {
        return in_array($user_id, $this->getOrganisers(['fields' => 'ID']));
    }

    public function getLocation()
    {
        $type = $this->getField('location_type');

        if ($type === 'input') {
            return $this->getField('location_input');
        }

        if ($type === 'id') {
            $location_id = $this->getField('location_id');
            if ($location_id && get_post_type($location_id)) {
                $location = new Post($location_id);
                return $location->getField('address');
            }
        }

        return false;
    }

    public function setLocation($value, $type = 'input')
    {
        if (in_array($type, ['input', 'id'])) {
            if ($type === 'input') {
                return $this->updateField('location_input', $value);
            }

            if ($type === 'id') {
                return $this->updateField('location_id', $value);
            }

            $this->updateField('location_type', $type);
        }

        return false;
    }

    public function getInvitees($args = [])
    {
        return Model::getInviteesByEvent($this->ID, $args);
    }

    public function getInviteesByStatus($status, $args = [])
    {
        $invitees = $this->getInvitees(['fields' => 'ids']);

        if (! $invitees) {
            return [];
        }

        return Model::getInviteesByStatus($status, [
            'include' => $invitees,
        ] + $args);
    }

    public function getInviteesByUser($user_id, $args = [])
    {
        $invitees = $this->getInvitees(['fields' => 'ids']);

        if (! $invitees) {
            return [];
        }

        return Model::getInviteesByUser($user_id, [
            'include' => $invitees,
        ] + $args);
    }

    public function getInviteeByUser($user_id)
    {
        $invitee = current($this->getInviteesByUser($user_id));

        return $invitee ? new Invitee($invitee) : null;
    }

    public function addInvitee($user_id, $status = 'pending')
    {
        $invitee = $this->getInviteeByUser($user_id);

        if ($invitee) {
            return $invitee->ID;
        }

        $status = apply_filters('my_events/add_invitee_status', $status, $this);

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

        return $invitee->ID;
    }

    public function updateInvitee($user_id, $status)
    {
        $invitee = $this->getInviteeByUser($user_id);

        if ($invitee) {
            return $invitee->setStatus($status);
        }

        return false;
    }

    public function removeInvitee($post_id)
    {
        $invitee = get_post($post_id);

        if ($invitee && get_post_type($invitee) === 'invitee') {
            $invitee = new Invitee($invitee);
            do_action('my_events/invitee_removed', $invitee, $invitee->getUser(), $this);
            return wp_delete_post($invitee->ID, true);
        }

        return false;
    }

    public function isInvitee($user_id)
    {
        return $this->getInviteeByUser($user_id) ? true : false;
    }

    public function getDefaultInviteeStatus()
    {
        return $this->getField('invitee_default_status');
    }

    public function setInvitees($user_ids, $status = null)
    {
        if (! $status) {
            $status = $this->getDefaultInviteeStatus();
        }

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
            'fields'  => 'ids',
        ]);

        foreach ($delete as $invitee_id) {
            $this->removeInvitee($invitee_id);
        }
    }

    public function removeInviteeByUser($user_id)
    {
        $invitee = $this->getInviteeByUser($user_id);

        return $this->removeInvitee($invitee->ID);
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

    public function getParticipants($args = [])
    {
        return $this->getInviteesUsers('accepted', $args);
    }

    public function isparticipant($user_id)
    {
        return in_array($user_id, $this->getParticipants(['fields' => 'ID']));
    }

    public function isMember($user_id)
    {
        return $this->isOrganiser($user_id) || $this->isparticipant($user_id);
    }

    public function isOver()
    {
        return $this->getEndTime('U') < date_i18n('U');
    }

    public function isPrivate()
    {
        return $this->getField('is_private') ? true : false;
    }

    public function hasAccess($user_id)
    {
        if (! $this->isPrivate()) {
            return true;
        }

        return $this->isMember($user_id);
    }

    public function isLimitedParticipants()
    {
        return $this->getField('limit_subscriptions') ? true : false;
    }

    public function getMaxParticipants()
    {
        return $this->getField('max_subscriptions');
    }

    public function acceptInvitation($user_id)
    {
        if (! $this->hasAccess($user_id)) {
            return new WP_Error(__FUNCTION__, __('You have no access to this event.', 'my-events'));
        }

        if ($this->isOver()) {
            return new WP_Error(__FUNCTION__, __('This event is over.', 'my-events'));
        }

        $invitee = $this->getInviteeByUser($user_id);

        if (! $invitee) {
            return new WP_Error(__FUNCTION__, __('You are not invited to this event.', 'my-events'));
        }

        if ($invitee->getStatus() === 'accepted') {
            return true;
        }

        if ($this->isLimitedParticipants() && count($this->getParticipants()) >= $this->getMaxParticipants()) {
            return new \WP_Error(__FILE__, __('The maximum amount of participants is reached', 'my-events'));
        }

        $invitee->setStatus('accepted');

        do_action('my_events/invitee_accepted_invitation', $invitee, $invitee->getUser(), $this);

        return true;
    }

    public function declineInvitation($user_id)
    {
        if (! $this->hasAccess($user_id)) {
            return new WP_Error(__FUNCTION__, __('You have no access to this event.', 'my-events'));
        }

        if ($this->isOver()) {
            return new WP_Error(__FUNCTION__, __('This event is over.', 'my-events'));
        }

        $invitee = $this->getInviteeByUser($user_id);

        if (! $invitee) {
            return new WP_Error(__FUNCTION__, __('You are not invited to this event.', 'my-events'));
        }

        if ($invitee->getStatus() === 'declined') {
            return true;
        }

        $invitee->setStatus('declined');

        do_action('my_events/invitee_declined_invitation', $invitee, $invitee->getUser(), $this);

        return true;
    }
}
