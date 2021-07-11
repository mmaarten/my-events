<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Events
{
    public static function init()
    {
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('wp_trash_post', [__CLASS__, 'trashPost']);
        add_action('before_delete_post', [__CLASS__, 'beforeDeletePost']);
        add_action('delete_user', [__CLASS__, 'beforeDeleteUser'], 10, 3);
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
        add_action('admin_notices', [__CLASS__, 'adminNotices']);

        add_filter('acf/load_value/key=my_events_event_individual_invitees', [__CLASS__, 'populateInviteesField'], 10, 3);
        add_filter('post_class', [__CLASS__, 'postClass'], 10, 3);
    }

    public static function getEventClasses($post_id)
    {
        $event = new Event($post_id);

        $classes = [];

        if ($event->isOver()) {
            $classes[] = 'is-event-over';
        }

        if ($event->isPrivate()) {
            $classes[] = 'is-private-event';
        }

        if ($event->hasMaxParticipants()) {
            $classes[] = 'is-subscriptions-enabled';
        } else {
            $classes[] = 'is-subscriptions-disabled';
        }

        if (is_user_logged_in()) {
            $invitee = $event->getInviteeByUser(get_current_user_id());
            if ($invitee) {
                $classes[] = 'is-invitee';
                $classes[] = sprintf('is-invitee-%s', $invitee->getStatus());
            }
        }

        return apply_filters('my_events/event_class', $classes, $event);
    }

    public static function savePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                self::updateEventFields($post_id);
                break;
            case 'invitee_group':
                self::updateInviteesFromInviteeGroup($post_id);
                break;
        }
    }

    public static function trashPost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'invitee_group':
                $events = Model::getEventsByInviteeGroup($post_id, ['post_status' => 'any']);
                foreach ($events as $event) {
                    $event = new Event($event);
                    // Set invitee type to 'individual'.
                    // The field 'individual_invitees' is automatically populated. So we dont need to update it.
                    $event->updateField('invitee_type', 'individual');
                }
                break;
            case 'event_location':
                // Get address setting from location
                $location = new Post($post_id);
                $address = $location->getField('address');
                $events = Model::getEventsByLocation($post_id, ['post_status' => 'any']);
                foreach ($events as $event) {
                    // Switch to 'custom' and save location address.
                    $event = new Event($event);
                    $event->updateField('location_type', 'custom');
                    $event->updateField('custom_location', $address);
                }
                break;
        }
    }

    public static function beforeDeletePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                // Remove all event related invitees.
                $event = new Event($post_id);
                $event->setInvitees([]);
                break;
        }
    }

    public static function beforeDeleteUser($user_id, $reassign, $user)
    {
        // Remove all user related invitees.

        $invitees = Model::getInviteesByUser($user_id, ['fields' => 'ids']);

        foreach ($invitees as $invitee) {
            wp_delete_post($invitee, true);
        }
    }

    public static function getInviteesFromSettingsField($event_id)
    {
        // Get event.
        $event = new Event($event_id);

        // Get invitee type.
        $type = $event->getField('invitee_type');

        $user_ids = [];

        // Get user ids from individual invitees field
        if ($type == 'individual') {
            $user_ids = $event->getField('individual_invitees');
        }

        // Get user ids from invitees group
        if ($type == 'group') {
            $group = new Post($event->getField('invitee_group'));
            $user_ids = $group->getField('users');
        }

        if (! $user_ids || ! is_array($user_ids)) {
            return [];
        }

        // Make sure all users exist.
        return get_users([
            'include' => $user_ids,
            'fields'  => 'ID',
        ]);
    }

    public static function updateEventFields($post_id)
    {
        $event = new Event($post_id);

        if ($event->isAllDay()) {
            $start_date = date('Y-m-d', strtotime($event->getField('start')));
            $end_date   = date('Y-m-d', strtotime($event->getField('end')));
            $event->updateField('start', "$start_date 00:00:00");
            $event->updateField('end', "$end_date 23:59:59");
        }

        if ($event->areSubscriptionsEnabled()) {
            $user_ids = self::getInviteesFromSettingsField($event->ID);
            $event->setInvitees($user_ids);
            $event->deleteField('individual_invitees');
        } else {
            $event->deleteField('organisers');
            $event->deleteField('invitee_type');
            $event->deleteField('individual_invitees');
            $event->deleteField('invitee_group');
            $event->deleteField('invitee_default_status');
            $event->deleteField('max_participants');
            $event->deleteField('location_type');
            $event->deleteField('custom_location');
            $event->deleteField('location_id');
            $event->deleteField('is_private');
            $event->setInvitees([]);
        }
    }

    public static function populateInviteesField($value, $post_id, $field)
    {
        // Check post type.
        if (get_post_type($post_id) != 'event') {
            return $value;
        }

        // Don't change field value on post save. We need to access the field settings.
        if (did_action('acf/save_post')) {
            return $value;
        }

        // Get event.
        $event = new Event($post_id);

        // Return invitees user ids.
        return $event->getInviteesUsers(null, ['fields' => 'ID']);
    }

    public static function updateInviteesFromInviteeGroup($group_id)
    {
        $group = new Post($group_id);

        $prev_users = $group->getMeta('_prev_users', true);
        $curr_users = $group->getField('users');

        if (! is_array($prev_users)) {
            $prev_users = $curr_users;
        }

        if (! is_array($curr_users)) {
            $curr_users = [];
        }

        // Check for differences.
        $add_users   = array_diff($curr_users, $prev_users);
        $remove_users = array_diff($prev_users, $curr_users);

        // Save current users.
        $group->updateMeta('_prev_users', $current_users);

        if (! $add_users && ! $prev_users) {
            return;
        }

        // Get all events related to the invitee group.
        $events = Model::getEventsByInviteeGroup($group->ID, ['post_status' => 'any']);

        // Add invitees.
        foreach ($add_users as $user_id) {
            foreach ($events as $event) {
                $event = new Event($event);
                if (! $event->isOver()) {
                    $event->addInvitee($user_id);
                }
            }
        }

        // Remove invitees.
        foreach ($remove_users as $user_id) {
            foreach ($events as $event) {
                $event = new Event($event);
                if (! $event->isOver()) {
                    $event->removeInviteeByUser($user_id);
                }
            }
        }
    }

    public static function renderInvities($post)
    {
        $event = new Event($post);

        Helpers::loadTemplate('event-edit-invitees', [
            'event' => $event,
        ]);
    }

    public static function addMetaBoxes($post_type)
    {
        add_meta_box('my-events-invitees', __('Invitees', 'my-events'), [__CLASS__, 'renderInvities'], 'event', 'side');
    }

    public static function adminNotices()
    {
        $screen = get_current_screen();

        if ($screen->base != 'post' || ! isset($_GET['post'])) {
            return;
        }

        $post_id = $_GET['post'];

        switch ($screen->post_type) {
            case 'event':
                $event = new Event($post_id);

                if ($event->isOver()) {
                    echo Helpers::adminNotice(__('This event is over.', 'my-events'), 'warning');
                }

                break;
        }
    }

    public static function postClass($classes, $class, $post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                $classes = array_merge($classes, self::getEventClasses($post_id));
                break;
        }

        return $classes;
    }
}
