<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Events
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('wp_trash_post', [__CLASS__, 'trashPost']);
        add_action('before_delete_post', [__CLASS__, 'deletePost']);
        add_action('delete_user', [__CLASS__, 'deleteUser']);

        add_filter('acf/load_value/key=my_events_event_individual_invitees_field', [__CLASS__, 'populateIndividualInviteesField'], 10, 3);
    }

    public static function getEventClasses($event_id)
    {
        return [];
    }

    /**
     * Populate individual invitees field
     *
     * @param array $value
     * @param int   $post_id
     * @param array $field
     * @return array
     */
    public static function populateIndividualInviteesField($value, $post_id, $field)
    {
        if (get_post_type($post_id) != 'event') {
            return $value;
        }

        if (did_action('acf/save_post')) {
            return $value;
        }

        $event = new Event($post_id);

        return $event->getInviteesUsers(null, ['fields' => 'ID']);
    }

    /**
     * Save post
     *
     * @param int $post_id
     */
    public static function savePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                $event = new Event($post_id);

                if ($event->isAllDay()) {
                    $start_date = $event->getStartTime('Y-m-d');
                    $end_date   = $event->getEndTime('Y-m-d');

                    $event->updateField('start', "$start_date 00:00:00");
                    $event->updateField('end', "$end_date 23:59:59");
                }

                $invitee_type = $event->getField('invitee_type');
                $invitees = [];

                if ($invitee_type == 'individual') {
                    $invitees = $event->getField('individual_invitees', false);
                }

                if ($invitee_type == 'group') {
                    $group_id = $event->getField('invitee_group', false);
                    if ($group_id && get_post_type($group_id)) {
                        $group = new Post($group_id);
                        $invitees = $group->getField('users', false);
                    }
                }

                $event->setInvitees($invitees);
                $event->deleteField('individual_invitees');
                break;
            case 'invitee_group':
                $group = new Post($post_id);
                $prev_users = $group->getField('_prev_users');
                $curr_users = $group->getField('users', false);

                if (! is_array($prev_users)) {
                    $prev_users = $curr_users;
                }

                $group->updateField('_prev_users', $curr_users);

                if ($prev_users !== $curr_users) {
                    $events = self::getEventsByInviteeGroup($group->ID);
                    foreach ($events as $event) {
                        $event = new Event($event);
                        if (! $event->isOver()) {
                            $event->setInvitees($curr_users);
                        }
                    }
                }
                break;
        }
    }

    /**
     * Trash post
     *
     * @param int $post_id
     */
    public static function trashPost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'invitee_group':
                // Switch events invitee type setting.
                $events = Model::getEventsByInviteeGroup($post_id, ['post_status' => 'any']);
                foreach ($events as $event) {
                    $event = new Event($event);
                    $event->updateField('invitee_type', 'individual');
                }
                break;
            case 'event_location':
                // Switch events location type setting.
                $location = new Post($post_id);
                $events = Model::getEventsByLocation($location->ID, ['post_status' => 'any']);
                foreach ($events as $event) {
                    $event = new Event($event);
                    $event->updateField('location_type', 'custom');
                    $event->updateField('custom_location', $location->getField('address', false));
                }
                break;
        }
    }

    /**
     * Delete post
     *
     * @param int $post_id
     */
    public static function deletePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                // Remove all event related invitees.
                $event = new Event($post_id);
                $event->setInvitees([]);
                break;
        }
    }

    /**
     * Delete user
     *
     * @param int $user_id
     */
    public static function deleteUser($user_id)
    {
        // Remove all user related invitees.
        $invitees = Model::getInviteesByUser($user_id, ['post_status' => 'any']);
        foreach ($invitees as $invitee) {
            $invitee = new Invitee($invitee);
            $event_id = $invitee->getEvent();
            if ($event_id) {
                $event = new Event($event_id);
                $event->removeInvitee($user_id);
            } else {
                wp_delete_post($invitee, true);
            }
        }

        // TODO : update invitee group 'users' setting?
    }
}
