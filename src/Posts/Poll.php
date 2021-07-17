<?php

namespace My\Events\Posts;

class Poll extends Post
{
    public function getTimes()
    {
        $value = $this->getField('times');

        return is_array($value) ? $value : [];
    }

    /**
     * Get invitees
     *
     * @param array $args
     * @return array
     */
    public function getInvitees($args = [])
    {
        return get_posts($args + [
            'post_type'    => 'poll_invitee',
            'status'       => 'publish',
            'numberposts'  => 999,
            'meta_key'     => 'poll',
            'meta_compare' => '=',
            'meta_value'   => $this->ID,
        ]);
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
        return current($this->getInviteesByUser($user_id, ['numberposts' => 1]));
    }

    /**
     * Get invitee by post
     *
     * @param int $invitee_id
     * @return mixed
     */
    public function getInviteeByPost($invitee_id)
    {
        return current($this->getInvitees([
            'include'     => [$invitee_id],
            'numberposts' => 1,
        ]));
    }

    /**
     * Get invitees users
     *
     * @param string $status
     * @param array  $args
     * @return array
     */
    public function getInviteesUsers($args = [])
    {
        $invitees = $this->getInvitees();

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
     * @param int $user_id
     * @return int
     */
    public function addInvitee($user_id)
    {
        $invitee = $this->getInviteeByUser($user_id);

        if ($invitee) {
            return $invitee->ID;
        }

        $post_id = wp_insert_post([
            'post_title'   => '',
            'post_content' => '',
            'post_type'    => 'poll_invitee',
            'post_status'  => 'publish',
        ]);

        $invitee = new Post($post_id);
        $invitee->updateField('poll', $this->ID);
        $invitee->updateField('user', $user_id);

        do_action('my_events/poll/invitee_added', $invitee, $user_id, $this);

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

        do_action('my_events/poll/invitee_removed', $invitee, $invitee->getField('user'), $this);

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
     * Is invitee
     *
     * @param int $user_id
     * @return bool
     */
    public function isInvitee($user_id)
    {
        return $this->getInviteeByUser($user_id) ? true : false;
    }

    public function getTimeFromUntil($index)
    {
        $times = $this->getTimes();

        if (! isset($times[$index])) {
            return false;
        }

        $start_date = $this->getStartTime($index, get_option('date_format'));
        $end_date   = $this->getEndTime($index, get_option('date_format'));

        if ($start_date == $end_date) {
            return sprintf(
                __('%1$s from %2$s until %3$s', 'my-events'),
                $start_date,
                $this->getStartTime($index, get_option('time_format')),
                $this->getEndTime($index, get_option('time_format'))
            );
        }

        return sprintf(__('from %1$s until %2$s', 'my-events'), $this->getStartTime($index), $this->getEndTime($index));
    }

    public function getStartTime($index, $format = '')
    {
        $times = $this->getTimes();

        if (! isset($times[$index])) {
            return false;
        }

        $time = $times[$index];

        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        return date_i18n($format, strtotime($time['start']));
    }

    public function getEndTime($index, $format = '')
    {
        $times = $this->getTimes();

        if (! isset($times[$index])) {
            return false;
        }

        $time = $times[$index];

        if (! $format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        return date_i18n($format, strtotime($time['end']));
    }

    public function getTimeUsers($index, $args = [])
    {
        $data = get_post_meta($this->ID, 'users', true);

        if (! is_array($data)) {
            $data = [];
        }

        if (empty($data[$index]) || ! is_array($data[$index])) {
            return [];
        }

        return get_users(['include' => $data[$index]] + $args);
    }

    public function addTimeUser($user_id, $index)
    {
        $data = get_post_meta($this->ID, 'users', true);

        if (! is_array($data)) {
            $data = [];
        }

        if (! isset($data[$index]) || ! is_array($data[$index])) {
            $data[$index] = [];
        }

        if (in_array($user_id, $data[$index])) {
            return true;
        }

        $data[$index][] = $user_id;

        update_post_meta($this->ID, 'users', $data);
    }

    public function removeTimeUser($user_id, $index)
    {
        $data = get_post_meta($this->ID, 'users', true);

        if (! is_array($data)) {
            $data = [];
        }

        if (! isset($data[$index]) || ! is_array($data[$index])) {
            return false;
        }

        $offset = array_search($user_id, $data[$index]);

        array_splice($data[$index], $offset, 1);

        return update_post_meta($this->ID, 'users', $data);
    }
}
